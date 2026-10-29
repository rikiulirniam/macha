<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\ExpLog;
use App\Models\Like;
use App\Models\Reading;
use App\Models\ReadingProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Get current ISO week key for Asia/Jakarta (e.g. 2026-W44).
     */
    public function getWeekKey(?Carbon $date = null): string
    {
        $date ??= Carbon::now('Asia/Jakarta');

        return $date->setTimezone('Asia/Jakarta')->isoFormat('GGGG-[W]WW');
    }

    /**
     * Calculate user level based on total EXP.
     */
    public function calculateLevel(int $totalExp): int
    {
        return (int) (floor($totalExp / config('matcha.gamification.level_divisor', 100)) + 1);
    }

    /**
     * Award EXP to a user in a safe database transaction.
     */
    public function awardExp(User $user, int $amount, string $source, ?int $readingId = null, ?Carbon $date = null): ExpLog
    {
        $date ??= Carbon::now('Asia/Jakarta');
        $weekKey = $this->getWeekKey($date);

        return DB::transaction(function () use ($user, $amount, $source, $readingId, $weekKey, $date) {
            $log = ExpLog::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source' => $source,
                'reading_id' => $readingId,
                'week_key' => $weekKey,
                'created_at' => $date,
            ]);

            $user->increment('total_exp', $amount);

            return $log;
        });
    }

    /**
     * Record reading completion and award EXP if eligible.
     *
     * @return array{success: bool, exp_awarded: int, daily_target_achieved: bool, badge_unlocked: ?Badge}
     */
    public function completeReading(User $user, Reading $reading): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        return DB::transaction(function () use ($user, $reading, $now, $todayStr) {
            $progress = ReadingProgress::firstOrCreate(
                ['user_id' => $user->id, 'reading_id' => $reading->id],
                ['last_slide' => 1]
            );

            // Validation: user cannot complete reading before reaching all slides
            $totalSlides = $reading->slides()->count();
            if ($progress->last_slide < $totalSlides) {
                // Auto mark last slide if at least 1 slide exists
                $progress->update(['last_slide' => $totalSlides]);
            }

            // If already completed previously, do not award EXP again
            $alreadyAwardedExp = ExpLog::where('user_id', $user->id)
                ->where('reading_id', $reading->id)
                ->where('source', 'reading_complete')
                ->exists();

            if ($progress->completed_at !== null || $alreadyAwardedExp) {
                if ($progress->completed_at === null) {
                    $progress->update(['completed_at' => $now]);
                }

                $userFresh = $user->fresh();

                return [
                    'success' => true,
                    'already_completed' => true,
                    'exp_awarded' => 0,
                    'daily_target_achieved' => false,
                    'badge_unlocked' => null,
                    'total_exp' => $userFresh->total_exp,
                    'current_streak' => $userFresh->current_streak,
                    'level' => $userFresh->level,
                    'progress_to_next_level' => $userFresh->progress_to_next_level,
                ];
            }

            // Mark completion
            $progress->update(['completed_at' => $now]);
            $reading->increment('completions_count');

            $expAwarded = 0;
            // Rule: Creator's own reading does NOT give EXP to themselves
            if ($reading->user_id !== $user->id) {
                $baseExp = (int) config('matcha.gamification.reading_complete_exp', 10);
                $this->awardExp($user, $baseExp, 'reading_complete', $reading->id, $now);
                $expAwarded += $baseExp;
            }

            // Update streak immediately upon reading & claiming EXP
            $this->updateStreak($user, $now);

            // Check daily target (1 reading per day activates daily reading target & badge)
            $dailyTargetAchieved = false;
            $completedTodayCount = ReadingProgress::where('user_id', $user->id)
                ->whereDate('completed_at', $todayStr)
                ->count();

            $targetCount = (int) config('matcha.gamification.daily_target_count', 1);
            if ($completedTodayCount >= $targetCount) {
                $dailyTargetAchieved = true;
                $bonusExp = (int) config('matcha.gamification.daily_target_bonus_exp', 0);
                if ($bonusExp > 0) {
                    $alreadyAwardedBonusToday = ExpLog::where('user_id', $user->id)
                        ->where('source', 'daily_target')
                        ->whereDate('created_at', $todayStr)
                        ->exists();

                    if (! $alreadyAwardedBonusToday) {
                        $this->awardExp($user, $bonusExp, 'daily_target', null, $now);
                        $expAwarded += $bonusExp;
                    }
                }
                $this->checkAndAwardBadge($user, 'daily_target');
            }

            // Check badges
            $unlockedBadge = $this->checkReadingBadges($user);
            if ($dailyTargetAchieved) {
                $unlockedBadge = $this->checkDailyTargetBadges($user) ?? $unlockedBadge;
            }
            $unlockedBadge = $this->checkStreakBadges($user) ?? $unlockedBadge;
            $unlockedBadge = $this->checkLevelBadges($user) ?? $unlockedBadge;

            $userFresh = $user->fresh();

            return [
                'success' => true,
                'already_completed' => false,
                'exp_awarded' => $expAwarded,
                'daily_target_achieved' => $dailyTargetAchieved,
                'badge_unlocked' => $unlockedBadge,
                'total_exp' => $userFresh->total_exp,
                'current_streak' => $userFresh->current_streak,
                'level' => $userFresh->level,
                'progress_to_next_level' => $userFresh->progress_to_next_level,
            ];
        });
    }

    /**
     * Submit an answer to a reading quiz and award EXP.
     *
     * @return array{is_correct: bool, exp_awarded: int, explanation: ?string, badge_unlocked: ?Badge, already_answered: bool, total_exp: int, current_streak: int, level: int, progress_to_next_level: int}
     */
    public function answerQuiz(User $user, Reading $reading, int $optionIndex): array
    {
        $quiz = $reading->quiz;
        if (! $quiz) {
            abort(404, 'Bacaan ini tidak memiliki kuis.');
        }

        $now = Carbon::now('Asia/Jakarta');

        return DB::transaction(function () use ($user, $reading, $quiz, $optionIndex, $now) {
            $progress = ReadingProgress::firstOrCreate(
                ['user_id' => $user->id, 'reading_id' => $reading->id],
                ['last_slide' => 1]
            );

            // If already answered, return previous result without awarding EXP again
            if ($progress->quiz_answered_at !== null) {
                $userFresh = $user->fresh();

                return [
                    'is_correct' => (bool) $progress->quiz_correct,
                    'exp_awarded' => 0,
                    'explanation' => $quiz->explanation,
                    'badge_unlocked' => null,
                    'already_answered' => true,
                    'total_exp' => $userFresh->total_exp,
                    'current_streak' => $userFresh->current_streak,
                    'level' => $userFresh->level,
                    'progress_to_next_level' => $userFresh->progress_to_next_level,
                ];
            }

            $isCorrect = ($optionIndex === $quiz->correct_index);
            $progress->update([
                'quiz_answered_at' => $now,
                'quiz_correct' => $isCorrect,
            ]);

            $expAwarded = 0;
            // Creator's own reading does not give EXP
            if ($reading->user_id !== $user->id) {
                $attemptExp = (int) config('matcha.gamification.quiz_attempt_exp', 5);
                $this->awardExp($user, $attemptExp, 'quiz_attempt', $reading->id, $now);
                $expAwarded += $attemptExp;

                if ($isCorrect) {
                    $correctBonus = (int) config('matcha.gamification.quiz_correct_bonus_exp', 5);
                    $this->awardExp($user, $correctBonus, 'quiz_correct', $reading->id, $now);
                    $expAwarded += $correctBonus;
                }

                // First EXP claim on reading quiz also maintains/activates daily streak
                $this->updateStreak($user, $now);
            }

            $unlockedBadge = null;
            if ($isCorrect) {
                $unlockedBadge = $this->checkQuizBadges($user);
            }
            $unlockedBadge = $this->checkLevelBadges($user) ?? $unlockedBadge;

            $userFresh = $user->fresh();

            return [
                'is_correct' => $isCorrect,
                'exp_awarded' => $expAwarded,
                'explanation' => $quiz->explanation,
                'badge_unlocked' => $unlockedBadge,
                'already_answered' => false,
                'total_exp' => $userFresh->total_exp,
                'current_streak' => $userFresh->current_streak,
                'level' => $userFresh->level,
                'progress_to_next_level' => $userFresh->progress_to_next_level,
            ];
        });
    }

    /**
     * Update user daily streak according to Asia/Jakarta calendar.
     */
    public function updateStreak(User $user, ?Carbon $date = null): void
    {
        $date ??= Carbon::now('Asia/Jakarta');
        $today = $date->copy()->setTimezone('Asia/Jakarta')->startOfDay();

        if (! $user->last_read_date) {
            $user->current_streak = 1;
            $user->longest_streak = max(1, $user->longest_streak);
            $user->last_read_date = $today->toDateString();
            $user->save();

            return;
        }

        $lastRead = Carbon::parse($user->last_read_date, 'Asia/Jakarta')->startOfDay();

        // Already read today -> streak remains the same
        if ($lastRead->equalTo($today)) {
            return;
        }

        $yesterday = $today->copy()->subDay();

        if ($lastRead->equalTo($yesterday)) {
            // Read yesterday -> streak increments!
            $user->current_streak += 1;
            if ($user->current_streak > $user->longest_streak) {
                $user->longest_streak = $user->current_streak;
            }
        } else {
            // Missed more than 1 day -> reset streak to 1
            $user->current_streak = 1;
        }

        $user->last_read_date = $today->toDateString();
        $user->save();

        // Check streak badges
        $this->checkStreakBadges($user);
    }

    /**
     * Check and award progressive badges for a category based on the user's current progress count.
     * Returns the highest newly unlocked badge if any.
     */
    public function checkProgressiveBadges(User $user, string $category, int $currentCount): ?Badge
    {
        $badges = Badge::where('category', $category)
            ->orderBy('tier', 'asc')
            ->get();

        if ($badges->isEmpty()) {
            return null;
        }

        $userBadgeIds = $user->badges()->pluck('badges.id')->all();
        $newlyUnlocked = null;

        foreach ($badges as $badge) {
            if ($currentCount >= $badge->required_count && ! in_array($badge->id, $userBadgeIds, true)) {
                $user->badges()->attach($badge->id, [
                    'earned_at' => Carbon::now('Asia/Jakarta'),
                ]);
                $userBadgeIds[] = $badge->id;
                $newlyUnlocked = $badge;
            }
        }

        return $newlyUnlocked;
    }

    /**
     * Check progressive reading badges.
     */
    public function checkReadingBadges(User $user): ?Badge
    {
        $count = ReadingProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        return $this->checkProgressiveBadges($user, 'reading', $count);
    }

    /**
     * Check progressive streak badges.
     */
    public function checkStreakBadges(User $user): ?Badge
    {
        $count = max((int) $user->current_streak, (int) $user->longest_streak);

        return $this->checkProgressiveBadges($user, 'streak', $count);
    }

    /**
     * Check progressive quiz badges.
     */
    public function checkQuizBadges(User $user): ?Badge
    {
        $count = ReadingProgress::where('user_id', $user->id)
            ->where('quiz_correct', true)
            ->count();

        return $this->checkProgressiveBadges($user, 'quiz', $count);
    }

    /**
     * Check progressive comment badges.
     */
    public function checkCommentBadges(User $user): ?Badge
    {
        $count = Comment::where('user_id', $user->id)->count();

        return $this->checkProgressiveBadges($user, 'comment', $count);
    }

    /**
     * Check progressive creator / publication badges.
     */
    public function checkCreatorBadges(User $user): ?Badge
    {
        $count = Reading::where('user_id', $user->id)
            ->where('status', 'published')
            ->count();

        return $this->checkProgressiveBadges($user, 'creator', $count);
    }

    /**
     * Check progressive likes given badges.
     */
    public function checkLikeGivenBadges(User $user): ?Badge
    {
        $count = Like::where('user_id', $user->id)->count();

        return $this->checkProgressiveBadges($user, 'like_given', $count);
    }

    /**
     * Check progressive likes received badges.
     */
    public function checkLikeReceivedBadges(User $user): ?Badge
    {
        $count = Like::whereIn('reading_id', $user->readings()->select('id'))->count();

        return $this->checkProgressiveBadges($user, 'like_received', $count);
    }

    /**
     * Check progressive level badges.
     */
    public function checkLevelBadges(User $user): ?Badge
    {
        $level = $this->calculateLevel((int) $user->total_exp);

        return $this->checkProgressiveBadges($user, 'level', $level);
    }

    /**
     * Check progressive daily target badges.
     */
    public function checkDailyTargetBadges(User $user): ?Badge
    {
        $dailyCount = ExpLog::where('user_id', $user->id)
            ->where('source', 'daily_target')
            ->count();

        $distinctDays = (int) (ReadingProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->selectRaw('COUNT(DISTINCT DATE(completed_at)) as cnt')
            ->value('cnt') ?? 0);

        $count = max($dailyCount, $distinctDays);

        return $this->checkProgressiveBadges($user, 'daily_target', $count);
    }

    /**
     * Check and award a specific badge if not already earned (backward compatibility).
     */
    public function checkAndAwardBadge(User $user, string $badgeKey): ?Badge
    {
        $badge = Badge::where('key', $badgeKey)->first();
        if (! $badge) {
            return null;
        }

        $alreadyEarned = $user->badges()->where('badges.id', $badge->id)->exists();
        if ($alreadyEarned) {
            return null;
        }

        $user->badges()->attach($badge->id, [
            'earned_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return $badge;
    }

    /**
     * Get progressive achievement categories with user's current progress,
     * active/next target badge, requirements, and completion status.
     *
     * @return array<string, array{
     *     key: string,
     *     title: string,
     *     unit: string,
     *     icon: string,
     *     current_count: int,
     *     active_badge: ?Badge,
     *     active_tier: int,
     *     required_count: int,
     *     is_maxed: bool,
     *     progress_percent: int,
     *     badges: Collection<int, Badge>
     * }>
     */
    public function getProgressiveCategories(User $user): array
    {
        $readingProgressStats = ReadingProgress::where('user_id', $user->id)
            ->selectRaw('COUNT(completed_at) as completed_count, COUNT(CASE WHEN quiz_correct = 1 THEN 1 END) as quiz_count, COUNT(DISTINCT DATE(completed_at)) as distinct_days')
            ->first();

        $completedReadings = (int) ($readingProgressStats->completed_count ?? 0);
        $correctQuizzes = (int) ($readingProgressStats->quiz_count ?? 0);
        $distinctDays = (int) ($readingProgressStats->distinct_days ?? 0);
        $streak = max((int) $user->current_streak, (int) $user->longest_streak);
        $commentsCount = Comment::where('user_id', $user->id)->count();
        $creationsCount = Reading::where('user_id', $user->id)->where('status', 'published')->count();
        $likesGiven = Like::where('user_id', $user->id)->count();
        $likesReceived = Like::whereIn('reading_id', $user->readings()->select('id'))->count();
        $level = $this->calculateLevel((int) $user->total_exp);
        $dailyTargetCount = max(
            ExpLog::where('user_id', $user->id)->where('source', 'daily_target')->count(),
            $distinctDays
        );

        $counts = [
            'reading' => $completedReadings,
            'streak' => $streak,
            'quiz' => $correctQuizzes,
            'comment' => $commentsCount,
            'creator' => $creationsCount,
            'like_given' => $likesGiven,
            'like_received' => $likesReceived,
            'level' => $level,
            'daily_target' => $dailyTargetCount,
        ];

        // Synchronize and auto-award qualifying badges
        foreach ($counts as $catKey => $count) {
            $this->checkProgressiveBadges($user, $catKey, $count);
        }

        // Fetch all badges grouped by category
        $allBadges = Badge::whereNotNull('category')
            ->orderBy('tier', 'asc')
            ->get()
            ->groupBy('category');

        $userBadges = $user->badges()->get()->keyBy('id');

        $categoriesMeta = [
            'reading' => ['title' => 'Membaca', 'unit' => 'bacaan', 'icon' => 'book-open'],
            'streak' => ['title' => 'Streak Harian', 'unit' => 'hari', 'icon' => 'flame'],
            'quiz' => ['title' => 'Kuis Pengetahuan', 'unit' => 'kuis benar', 'icon' => 'sparkles'],
            'comment' => ['title' => 'Diskusi & Komentar', 'unit' => 'komentar', 'icon' => 'message-circle'],
            'creator' => ['title' => 'Kreator Bacaan', 'unit' => 'karya terbit', 'icon' => 'pen-tool'],
            'like_given' => ['title' => 'Apresiasi Suka', 'unit' => 'suka diberikan', 'icon' => 'heart'],
            'like_received' => ['title' => 'Popularitas Karya', 'unit' => 'suka diterima', 'icon' => 'star'],
            'level' => ['title' => 'Tingkat Level', 'unit' => 'level', 'icon' => 'trophy'],
            'daily_target' => ['title' => 'Target Harian', 'unit' => 'hari tercapai', 'icon' => 'check-circle-2'],
        ];

        $result = [];

        foreach ($categoriesMeta as $catKey => $meta) {
            $catBadges = $allBadges->get($catKey, collect())->map(function (Badge $b) use ($userBadges) {
                $earned = isset($userBadges[$b->id]);
                $b->is_earned = $earned;
                $b->earned_at = $earned ? $userBadges[$b->id]->pivot->earned_at : null;

                return $b;
            });

            $currentCount = $counts[$catKey] ?? 0;
            $unearnedBadge = $catBadges->firstWhere('is_earned', false);

            if (! $unearnedBadge) {
                $activeBadge = $catBadges->last();
                $isMaxed = true;
                $activeTier = $activeBadge?->tier ?? 1;
                $requiredCount = $activeBadge?->required_count ?? $currentCount;
                $progressPercent = 100;
            } else {
                $activeBadge = $unearnedBadge;
                $isMaxed = false;
                $activeTier = $activeBadge->tier;
                $requiredCount = max(1, (int) $activeBadge->required_count);
                $progressPercent = min(100, (int) round(($currentCount / $requiredCount) * 100));
            }

            $result[$catKey] = [
                'key' => $catKey,
                'title' => $meta['title'],
                'unit' => $meta['unit'],
                'icon' => $meta['icon'],
                'current_count' => $currentCount,
                'active_badge' => $activeBadge,
                'active_tier' => $activeTier,
                'required_count' => $requiredCount,
                'is_maxed' => $isMaxed,
                'progress_percent' => $progressPercent,
                'badges' => $catBadges,
            ];
        }

        return $result;
    }
}
