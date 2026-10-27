<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReadingRequest;
use App\Http\Requests\UpdateReadingRequest;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Reading;
use App\Models\ReadingProgress;
use App\Models\ReadingQuiz;
use App\Models\ReadingSlide;
use App\Models\Tag;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReadingController extends Controller
{
    public function __construct(
        protected GamificationService $gamificationService
    ) {}

    /**
     * Record slide progress (AJAX).
     */
    public function recordProgress(Request $request, Reading $reading): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        $slideNumber = (int) $request->input('slide', 1);

        $progress = ReadingProgress::firstOrCreate(
            ['user_id' => $user->id, 'reading_id' => $reading->id],
            ['last_slide' => 1]
        );

        if ($slideNumber > $progress->last_slide) {
            $progress->update(['last_slide' => $slideNumber]);
        }

        return response()->json([
            'success' => true,
            'last_slide' => $progress->last_slide,
        ]);
    }

    /**
     * Complete reading and award EXP (AJAX).
     */
    public function complete(Request $request, Reading $reading): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $result = $this->gamificationService->completeReading($user, $reading);

        return response()->json($result);
    }

    /**
     * Answer quiz and award EXP (AJAX).
     */
    public function quiz(Request $request, Reading $reading): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'option_index' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $result = $this->gamificationService->answerQuiz($user, $reading, (int) $validated['option_index']);

        return response()->json($result);
    }

    /**
     * Toggle like on a reading (AJAX).
     */
    public function toggleLike(Request $request, Reading $reading): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        return DB::transaction(function () use ($user, $reading) {
            $existingLike = Like::where('user_id', $user->id)
                ->where('reading_id', $reading->id)
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                $reading->decrement('likes_count');
                $liked = false;
            } else {
                Like::create([
                    'user_id' => $user->id,
                    'reading_id' => $reading->id,
                ]);
                $reading->increment('likes_count');
                $liked = true;
            }

            return response()->json([
                'liked' => $liked,
                'likes_count' => max(0, $reading->fresh()->likes_count),
            ]);
        });
    }

    /**
     * List comments for a reading (AJAX).
     */
    public function comments(Reading $reading): JsonResponse
    {
        $user = auth()->user();

        $comments = $reading->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(function (Comment $c) use ($user, $reading) {
                return [
                    'id' => $c->id,
                    'body' => e($c->body),
                    'user' => [
                        'name' => $c->user->name,
                        'username' => $c->user->username,
                        'initials' => $c->user->initials,
                    ],
                    'created_at_human' => $c->created_at->locale('id')->diffForHumans(),
                    'can_delete' => $user && ($user->id === $c->user_id || $user->id === $reading->user_id),
                ];
            });

        return response()->json([
            'comments' => $comments,
            'total' => $comments->count(),
        ]);
    }

    /**
     * Store comment with rate limiting and banned words moderation (AJAX).
     */
    public function storeComment(Request $request, Reading $reading): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401);
        }

        // Rate limit: 10 comments per minute per user
        $rateLimitKey = 'comments:user:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'message' => "Terlalu cepat! Mohon tunggu {$seconds} detik sebelum berkomentar lagi.",
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:300'],
        ], [
            'body.required' => 'Komentar tidak boleh kosong.',
            'body.max' => 'Komentar maksimal 300 karakter.',
        ]);

        // Friendly Banned words filter
        $bannedWords = config('matcha.banned_words', []);
        $lowercaseBody = mb_strtolower($validated['body']);

        foreach ($bannedWords as $word) {
            if (Str::contains($lowercaseBody, mb_strtolower($word))) {
                return response()->json([
                    'message' => 'Komentarmu mengandung kata yang kurang pantas. Mari jaga percakapan tetap ramah dan nyaman ya!',
                ], 422);
            }
        }

        $comment = DB::transaction(function () use ($user, $reading, $validated) {
            $c = Comment::create([
                'user_id' => $user->id,
                'reading_id' => $reading->id,
                'body' => trim(strip_tags($validated['body'])),
            ]);

            $reading->increment('comments_count');

            // Award badge 'first_comment' if eligible
            $this->gamificationService->checkAndAwardBadge($user, 'first_comment');

            return $c;
        });

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => e($comment->body),
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'initials' => $user->initials,
                ],
                'created_at_human' => 'Baru saja',
                'can_delete' => true,
            ],
            'message' => 'Komentar berhasil dikirim!',
        ], 201);
    }

    /**
     * Delete comment (AJAX).
     */
    public function deleteComment(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $reading = $comment->reading;

        // Policy authorization: comment author or reading creator can delete
        if ($user->id !== $comment->user_id && $user->id !== $reading->user_id) {
            return response()->json(['message' => 'Kamu tidak berhak menghapus komentar ini.'], 403);
        }

        DB::transaction(function () use ($comment, $reading) {
            $comment->delete();
            $reading->decrement('comments_count');
        });

        return response()->json(['success' => true]);
    }

    /**
     * Public read-only page for sharing (/r/{slug}).
     */
    public function showPublic(string $slug): View
    {
        $reading = Reading::published()
            ->with(['user', 'tags', 'slides', 'quiz', 'likes', 'comments.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views count
        $reading->increment('views_count');

        return view('readings.public', [
            'reading' => $reading,
        ]);
    }

    /**
     * Creator: Create reading form.
     */
    public function create(): View
    {
        $tags = Tag::all();

        return view('readings.create', [
            'tags' => $tags,
        ]);
    }

    /**
     * Creator: Store reading.
     */
    public function store(StoreReadingRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $reading = DB::transaction(function () use ($user, $validated, $request) {
            $slug = Str::slug($validated['title']).'-'.Str::lower(Str::random(6));

            // Estimate reading minutes
            $totalWords = 0;
            $mediaSlides = 0;
            foreach ($validated['slides'] as $s) {
                if (! empty($s['body'])) {
                    $totalWords += str_word_count(strip_tags($s['body']));
                }
                if (in_array($s['type'], ['image', 'audio'])) {
                    $mediaSlides++;
                }
            }
            $minutes = max(1, min(5, (int) ceil(($totalWords / 200) + ($mediaSlides * 0.25))));

            $reading = Reading::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'reading_minutes' => $minutes,
                'published_at' => ($validated['status'] === 'published') ? Carbon::now('Asia/Jakarta') : null,
            ]);

            $reading->tags()->sync($validated['tags']);

            // Process slides
            foreach ($validated['slides'] as $position => $slideData) {
                $mediaPath = null;

                if ($slideData['type'] === 'image' && $request->hasFile("slides.{$position}.image")) {
                    $file = $request->file("slides.{$position}.image");
                    $mediaPath = $file->store('readings/images', 'public');
                } elseif ($slideData['type'] === 'audio' && $request->hasFile("slides.{$position}.audio")) {
                    $file = $request->file("slides.{$position}.audio");
                    $mediaPath = $file->store('readings/audio', 'public');
                }

                ReadingSlide::create([
                    'reading_id' => $reading->id,
                    'position' => $position + 1,
                    'type' => $slideData['type'],
                    'bg_color' => $slideData['bg_color'] ?? 'paper',
                    'body' => isset($slideData['body']) ? trim(strip_tags($slideData['body'])) : null,
                    'source' => isset($slideData['source']) ? trim(strip_tags($slideData['source'])) : null,
                    'media_path' => $mediaPath,
                ]);
            }

            // Optional quiz
            if (! empty($validated['has_quiz']) && ! empty($validated['quiz_question'])) {
                ReadingQuiz::create([
                    'reading_id' => $reading->id,
                    'question' => trim(strip_tags($validated['quiz_question'])),
                    'options' => array_values(array_filter($validated['quiz_options'] ?? [])),
                    'correct_index' => (int) ($validated['quiz_correct_index'] ?? 0),
                    'explanation' => isset($validated['quiz_explanation']) ? trim(strip_tags($validated['quiz_explanation'])) : null,
                ]);
            }

            // Award 'first_publish' badge if published
            if ($reading->status === 'published') {
                $this->gamificationService->checkAndAwardBadge($user, 'first_publish');
            }

            return $reading;
        });

        $message = ($reading->status === 'published')
            ? 'Bacaan berhasil dipublikasikan ke seluruh pembaca Macha!'
            : 'Draf bacaan berhasil disimpan!';

        return redirect()->route('readings.my')->with('success', $message);
    }

    /**
     * Creator: List my readings with statistics.
     */
    public function myReadings(Request $request): View
    {
        $user = $request->user();

        $readings = Reading::where('user_id', $user->id)
            ->with(['tags', 'slides', 'quiz'])
            ->withCount(['likes', 'comments', 'progresses'])
            ->latest()
            ->get();

        // Calculate quiz accuracy stats per reading
        foreach ($readings as $r) {
            $totalQuizAnswers = ReadingProgress::where('reading_id', $r->id)->whereNotNull('quiz_answered_at')->count();
            $correctQuizAnswers = ReadingProgress::where('reading_id', $r->id)->where('quiz_correct', true)->count();
            $r->quiz_accuracy_ratio = $totalQuizAnswers > 0 ? (int) round(($correctQuizAnswers / $totalQuizAnswers) * 100) : null;
        }

        return view('readings.my', [
            'readings' => $readings,
        ]);
    }

    /**
     * Creator: Edit reading form.
     */
    public function edit(Reading $reading): View
    {
        Gate::authorize('update', $reading);

        $tags = Tag::all();
        $reading->load(['tags', 'slides', 'quiz']);

        return view('readings.edit', [
            'reading' => $reading,
            'tags' => $tags,
        ]);
    }

    /**
     * Creator: Update reading.
     */
    public function update(UpdateReadingRequest $request, Reading $reading): RedirectResponse
    {
        Gate::authorize('update', $reading);

        $validated = $request->validated();

        DB::transaction(function () use ($reading, $validated, $request) {
            $totalWords = 0;
            $mediaSlides = 0;
            foreach ($validated['slides'] as $s) {
                if (! empty($s['body'])) {
                    $totalWords += str_word_count(strip_tags($s['body']));
                }
                if (in_array($s['type'], ['image', 'audio'])) {
                    $mediaSlides++;
                }
            }
            $minutes = max(1, min(5, (int) ceil(($totalWords / 200) + ($mediaSlides * 0.25))));

            $reading->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'reading_minutes' => $minutes,
                'published_at' => ($validated['status'] === 'published' && ! $reading->published_at) ? Carbon::now('Asia/Jakarta') : $reading->published_at,
            ]);

            $reading->tags()->sync($validated['tags']);

            // Recreate slides
            $existingSlides = $reading->slides()->get()->keyBy('position');
            $reading->slides()->delete();

            foreach ($validated['slides'] as $pos => $slideData) {
                $position = $pos + 1;
                $mediaPath = $existingSlides[$position]->media_path ?? null;

                if ($slideData['type'] === 'image' && $request->hasFile("slides.{$pos}.image")) {
                    $mediaPath = $request->file("slides.{$pos}.image")->store('readings/images', 'public');
                } elseif ($slideData['type'] === 'audio' && $request->hasFile("slides.{$pos}.audio")) {
                    $mediaPath = $request->file("slides.{$pos}.audio")->store('readings/audio', 'public');
                }

                ReadingSlide::create([
                    'reading_id' => $reading->id,
                    'position' => $position,
                    'type' => $slideData['type'],
                    'bg_color' => $slideData['bg_color'] ?? 'paper',
                    'body' => isset($slideData['body']) ? trim(strip_tags($slideData['body'])) : null,
                    'source' => isset($slideData['source']) ? trim(strip_tags($slideData['source'])) : null,
                    'media_path' => $mediaPath,
                ]);
            }

            // Quiz
            if (! empty($validated['has_quiz']) && ! empty($validated['quiz_question'])) {
                ReadingQuiz::updateOrCreate(
                    ['reading_id' => $reading->id],
                    [
                        'question' => trim(strip_tags($validated['quiz_question'])),
                        'options' => array_values(array_filter($validated['quiz_options'] ?? [])),
                        'correct_index' => (int) ($validated['quiz_correct_index'] ?? 0),
                        'explanation' => isset($validated['quiz_explanation']) ? trim(strip_tags($validated['quiz_explanation'])) : null,
                    ]
                );
            } else {
                $reading->quiz()?->delete();
            }
        });

        return redirect()->route('readings.my')->with('success', 'Perubahan bacaan berhasil disimpan!');
    }

    /**
     * Creator: Destroy reading (soft delete).
     */
    public function destroy(Reading $reading): RedirectResponse
    {
        Gate::authorize('delete', $reading);

        $reading->delete();

        return redirect()->route('readings.my')->with('success', 'Bacaan berhasil dihapus.');
    }
}
