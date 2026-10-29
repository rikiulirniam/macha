<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\ExpLog;
use App\Models\Reading;
use App\Models\ReadingQuiz;
use App\Models\ReadingSlide;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MachaFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_reading_awards_exp_only_once(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create(['total_exp' => 0]);

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Bacaan Uji',
            'slug' => 'bacaan-uji-1',
            'status' => 'published',
            'reading_minutes' => 2,
            'published_at' => now(),
        ]);

        ReadingSlide::create([
            'reading_id' => $reading->id,
            'position' => 1,
            'type' => 'text',
            'body' => 'Slide 1 konten.',
            'bg_color' => 'paper',
        ]);

        // First completion
        $response = $this->actingAs($reader)->postJson("/readings/{$reading->id}/complete");
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'already_completed' => false,
                'exp_awarded' => 10,
                'total_exp' => 10,
                'current_streak' => 1,
                'level' => 1,
                'progress_to_next_level' => 10,
            ]);

        $reader->refresh();
        $this->assertSame(10, $reader->total_exp);
        $this->assertSame(1, $reader->current_streak);

        // Second completion of same reading -> 0 EXP awarded
        $secondResponse = $this->actingAs($reader)->postJson("/readings/{$reading->id}/complete");
        $secondResponse->assertOk()
            ->assertJson([
                'success' => true,
                'already_completed' => true,
                'exp_awarded' => 0,
                'total_exp' => 10,
                'level' => 1,
                'progress_to_next_level' => 10,
            ]);

        $reader->refresh();
        $this->assertSame(10, $reader->total_exp);
    }

    public function test_own_reading_does_not_award_exp_to_creator(): void
    {
        $creator = User::factory()->create(['total_exp' => 0]);

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Karya Sendiri',
            'slug' => 'karya-sendiri',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        ReadingSlide::create([
            'reading_id' => $reading->id,
            'position' => 1,
            'type' => 'text',
            'body' => 'Isi karya sendiri.',
            'bg_color' => 'paper',
        ]);

        $response = $this->actingAs($creator)->postJson("/readings/{$reading->id}/complete");
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'exp_awarded' => 0,
            ]);

        $creator->refresh();
        $this->assertSame(0, $creator->total_exp);
    }

    public function test_quiz_answers_award_exp_only_once(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create(['total_exp' => 0]);

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Bacaan Kuis',
            'slug' => 'bacaan-kuis',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        ReadingQuiz::create([
            'reading_id' => $reading->id,
            'question' => 'Berapa 1+1?',
            'options' => ['1', '2', '3'],
            'correct_index' => 1,
            'explanation' => '1 ditambah 1 adalah 2.',
        ]);

        // First answer: correct (+5 attempt + 5 correct = +10 EXP)
        $response = $this->actingAs($reader)->postJson("/readings/{$reading->id}/quiz", [
            'option_index' => 1,
        ]);

        $response->assertOk()
            ->assertJson([
                'is_correct' => true,
                'exp_awarded' => 10,
            ]);

        $reader->refresh();
        $this->assertSame(10, $reader->total_exp);

        // Second answer: repeat attempt receives 0 EXP
        $secondResponse = $this->actingAs($reader)->postJson("/readings/{$reading->id}/quiz", [
            'option_index' => 1,
        ]);

        $secondResponse->assertOk()
            ->assertJson([
                'exp_awarded' => 0,
            ]);

        $reader->refresh();
        $this->assertSame(10, $reader->total_exp);
    }

    public function test_leaderboard_only_counts_current_week_exp(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);

        $currentWeekKey = Carbon::now('Asia/Jakarta')->isoFormat('GGGG-[W]WW');
        $lastWeekKey = Carbon::now('Asia/Jakarta')->subWeeks(2)->isoFormat('GGGG-[W]WW');

        // User A has 50 EXP this week
        ExpLog::create([
            'user_id' => $userA->id,
            'amount' => 50,
            'source' => 'reading_complete',
            'week_key' => $currentWeekKey,
            'created_at' => now(),
        ]);

        // User B has 100 EXP last week, but 0 this week
        ExpLog::create([
            'user_id' => $userB->id,
            'amount' => 100,
            'source' => 'reading_complete',
            'week_key' => $lastWeekKey,
            'created_at' => now()->subWeeks(2),
        ]);

        $response = $this->actingAs($userA)->get('/peringkat');
        $response->assertOk();

        // User A appears on leaderboard with 50 EXP
        $response->assertSee('User A');
        $response->assertSee('50 EXP');

        // User B must NOT appear in this week's leaderboard
        $response->assertDontSee('User B');

        // Leaderboard countdown must be integer days/hours, not float decimals
        $response->assertSee('Reset dalam');
        $this->assertDoesNotMatchRegularExpression('/Reset dalam \d+\.\d+ hari/', $response->getContent());
    }

    public function test_banned_words_in_comments_are_rejected(): void
    {
        $creator = User::factory()->create();
        $user = User::factory()->create();

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Artikel Menarik',
            'slug' => 'artikel-menarik-banned',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        // Attempt to post comment with banned word 'anjing'
        $response = $this->actingAs($user)->postJson("/readings/{$reading->id}/comments", [
            'body' => 'Tulisan kamu jelek sekali dasar anjing!',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('comments', [
            'reading_id' => $reading->id,
        ]);

        // Clean comment succeeds
        $cleanResponse = $this->actingAs($user)->postJson("/readings/{$reading->id}/comments", [
            'body' => 'Tulisan yang sangat bermanfaat, terima kasih!',
        ]);

        $cleanResponse->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'reading_id' => $reading->id,
            'body' => 'Tulisan yang sangat bermanfaat, terima kasih!',
        ]);
    }

    public function test_only_owner_can_edit_or_delete_reading(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $reading = Reading::create([
            'user_id' => $owner->id,
            'title' => 'Bacaan Asli',
            'slug' => 'bacaan-asli',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        // Other user attempts to view edit page -> 403 Forbidden
        $response = $this->actingAs($otherUser)->get("/readings/{$reading->id}/edit");
        $response->assertStatus(403);

        // Other user attempts to delete -> 403 Forbidden
        $deleteResponse = $this->actingAs($otherUser)->delete("/readings/{$reading->id}");
        $deleteResponse->assertStatus(403);

        $this->assertNotSoftDeleted('readings', ['id' => $reading->id]);

        // Owner can delete
        $ownerDeleteResponse = $this->actingAs($owner)->delete("/readings/{$reading->id}");
        $ownerDeleteResponse->assertRedirect(route('readings.my'));

        $this->assertSoftDeleted('readings', ['id' => $reading->id]);
    }

    public function test_comment_can_be_deleted_by_author_or_reading_creator(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();
        $stranger = User::factory()->create();

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Diskusi Santai',
            'slug' => 'diskusi-santai',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        $comment = Comment::create([
            'user_id' => $commenter->id,
            'reading_id' => $reading->id,
            'body' => 'Komentar pertama saya.',
        ]);

        // Stranger cannot delete comment -> 403
        $strangerDelete = $this->actingAs($stranger)->deleteJson("/comments/{$comment->id}");
        $strangerDelete->assertStatus(403);

        // Commenter can delete
        $commenterDelete = $this->actingAs($commenter)->deleteJson("/comments/{$comment->id}");
        $commenterDelete->assertOk();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_creator_can_upload_m4a_audio_slide(): void
    {
        Storage::fake('public');

        $creator = User::factory()->create();
        $tag = Tag::create(['name' => 'Teknologi', 'slug' => 'teknologi', 'icon' => 'cpu']);

        $m4aFile = UploadedFile::fake()->create('narasi.m4a', 2048, 'audio/mp4');

        $response = $this->actingAs($creator)->post('/readings', [
            'title' => 'Podcast Micro AI',
            'tags' => [$tag->id],
            'status' => 'published',
            'slides' => [
                [
                    'type' => 'audio',
                    'bg_color' => 'teal',
                    'body' => 'Transkrip rekaman suara tentang kecerdasan buatan.',
                    'audio' => $m4aFile,
                ],
            ],
        ]);

        $response->assertRedirect(route('readings.my'));

        $reading = Reading::where('title', 'Podcast Micro AI')->first();
        $this->assertNotNull($reading);

        $slide = $reading->slides()->first();
        $this->assertNotNull($slide);
        $this->assertSame('audio', $slide->type);
        $this->assertNotNull($slide->media_path);

        Storage::disk('public')->assertExists($slide->media_path);

        // Also test .m4a created by recorders with audio/x-m4a or video/mp4 MIME
        $recorderM4a = UploadedFile::fake()->create('voicenote.m4a', 3072, 'video/mp4');
        $response2 = $this->actingAs($creator)->post('/readings', [
            'title' => 'Voice Note Episode 2',
            'tags' => [$tag->id],
            'status' => 'published',
            'slides' => [
                [
                    'type' => 'audio',
                    'bg_color' => 'paper',
                    'body' => 'Episode kedua.',
                    'audio' => $recorderM4a,
                ],
            ],
        ]);
        $response2->assertRedirect(route('readings.my'));
        $reading2 = Reading::where('title', 'Voice Note Episode 2')->first();
        $this->assertNotNull($reading2);
        Storage::disk('public')->assertExists($reading2->slides()->first()->media_path);
    }

    public function test_draft_reading_retains_audio_and_image_when_published(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $tag = Tag::create(['name' => 'Design', 'slug' => 'design', 'icon' => 'palette']);

        $imageFile = UploadedFile::fake()->image('illustration.png', 400, 400);
        $audioFile = UploadedFile::fake()->create('track.m4a', 2048, 'audio/mp4');

        // 1. Create draft with image and audio
        $storeResponse = $this->actingAs($user)->post('/readings', [
            'title' => 'Draft Media Multi-Slide',
            'tags' => [$tag->id],
            'status' => 'draft',
            'slides' => [
                [
                    'type' => 'image',
                    'bg_color' => 'paper',
                    'body' => 'Gambar di draft',
                    'image' => $imageFile,
                ],
                [
                    'type' => 'audio',
                    'bg_color' => 'teal',
                    'body' => 'Audio di draft',
                    'audio' => $audioFile,
                ],
            ],
        ]);

        $storeResponse->assertRedirect(route('readings.my'));
        $reading = Reading::where('title', 'Draft Media Multi-Slide')->first();
        $this->assertNotNull($reading);
        $this->assertSame('draft', $reading->status);

        $initialSlides = $reading->slides()->orderBy('position')->get();
        $this->assertCount(2, $initialSlides);
        $savedImagePath = $initialSlides[0]->media_path;
        $savedAudioPath = $initialSlides[1]->media_path;
        $this->assertNotNull($savedImagePath);
        $this->assertNotNull($savedAudioPath);

        // 2. Publish the draft without re-uploading the files
        $updateResponse = $this->actingAs($user)->put("/readings/{$reading->id}", [
            'title' => 'Draft Media Multi-Slide Telah Terbit',
            'tags' => [$tag->id],
            'status' => 'published',
            'slides' => [
                [
                    'type' => 'image',
                    'bg_color' => 'paper',
                    'body' => 'Gambar di terbit',
                    'media_path' => $savedImagePath,
                ],
                [
                    'type' => 'audio',
                    'bg_color' => 'teal',
                    'body' => 'Audio di terbit',
                    'media_path' => $savedAudioPath,
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('readings.my'));

        $reading->refresh();
        $this->assertSame('published', $reading->status);
        $this->assertSame('Draft Media Multi-Slide Telah Terbit', $reading->title);

        $updatedSlides = $reading->slides()->orderBy('position')->get();
        $this->assertCount(2, $updatedSlides);
        $this->assertSame($savedImagePath, $updatedSlides[0]->media_path);
        $this->assertSame($savedAudioPath, $updatedSlides[1]->media_path);
    }
}
