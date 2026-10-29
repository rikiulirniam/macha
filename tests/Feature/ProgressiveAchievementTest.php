<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Reading;
use App\Models\ReadingSlide;
use App\Models\Tag;
use App\Models\User;
use App\Services\GamificationService;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressiveAchievementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BadgeSeeder::class);
    }

    public function test_first_comment_awards_and_returns_badge_toast_payload(): void
    {
        $creator = User::factory()->create();
        $commenter = User::factory()->create();

        $reading = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Bacaan Menarik',
            'slug' => 'bacaan-menarik',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($commenter)->postJson("/readings/{$reading->id}/comments", [
            'body' => 'Opini yang sangat mencerahkan!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('badge_unlocked.name', 'Komentator')
            ->assertJsonPath('badge_unlocked.icon', 'message-circle');

        $this->assertTrue(
            $commenter->badges()->where('key', 'first_comment')->exists()
        );

        // Second comment does not re-unlock tier 1 badge
        $secondResponse = $this->actingAs($commenter)->postJson("/readings/{$reading->id}/comments", [
            'body' => 'Komentar kedua saya.',
        ]);

        $secondResponse->assertCreated()
            ->assertJsonPath('badge_unlocked', null);
    }

    public function test_first_publish_flashes_achievement_toast_to_session(): void
    {
        $author = User::factory()->create();
        $tag = Tag::create(['name' => 'Edukasi', 'slug' => 'edukasi']);

        $payload = [
            'title' => 'Karya Pertama Saya',
            'description' => 'Ringkasan cerita',
            'status' => 'published',
            'tags' => [$tag->id],
            'slides' => [
                [
                    'type' => 'text',
                    'bg_color' => 'paper',
                    'body' => 'Konten slide pertama yang penuh inspirasi untuk seluruh pembaca.',
                ],
            ],
        ];

        $response = $this->actingAs($author)->post('/readings', $payload);

        $response->assertRedirect(route('readings.my'))
            ->assertSessionHas('achievement', function ($achievement) {
                return $achievement['name'] === 'Kreator Baru'
                    && $achievement['icon'] === 'pen-tool';
            });

        $this->assertTrue(
            $author->badges()->where('key', 'first_publish')->exists()
        );
    }

    public function test_progressive_reading_category_advances_to_next_tier(): void
    {
        $creator = User::factory()->create();
        $reader = User::factory()->create(['total_exp' => 0]);
        $service = app(GamificationService::class);

        $reading1 = Reading::create([
            'user_id' => $creator->id,
            'title' => 'Bacaan 1',
            'slug' => 'bacaan-1',
            'status' => 'published',
            'reading_minutes' => 1,
            'published_at' => now(),
        ]);
        ReadingSlide::create([
            'reading_id' => $reading1->id,
            'position' => 1,
            'type' => 'text',
            'body' => 'Slide 1',
        ]);

        // Complete 1 reading
        $this->actingAs($reader)->postJson("/readings/{$reading1->id}/complete");

        $reader->refresh();
        $this->assertTrue($reader->badges()->where('key', 'first_read')->exists());

        // Check progressive categories
        $categories = $service->getProgressiveCategories($reader);
        $readingCat = $categories['reading'];

        $this->assertSame(1, $readingCat['current_count']);
        $this->assertFalse($readingCat['is_maxed']);
        $this->assertSame(2, $readingCat['active_tier']);
        $this->assertSame('Kutu Buku', $readingCat['active_badge']->name);
        $this->assertSame(10, $readingCat['required_count']);
        $this->assertSame(10, $readingCat['progress_percent']);

        // Profile show view should render earned badges showcase and progressive category
        $profileResponse = $this->actingAs($reader)->get(route('profile.show'));
        $profileResponse->assertOk()
            ->assertSee('Lencana yang Diraih')
            ->assertSee('Pembaca Pertama')
            ->assertSee('Kutu Buku')
            ->assertSee('Progres Pencapaian (9 Kategori)');
    }
}
