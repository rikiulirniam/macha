<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ExpLog;
use App\Models\Reading;
use App\Models\ReadingProgress;
use App\Models\User;
use Carbon\Carbon;
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
            if ($progress->completed_at !== null) {
                return [
                    'success' => true,
                    'exp_awarded' => 0,
                    'daily_target_achieved' => false,
                    'badge_unlocked' => null,
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

            // Update streak
            $this->updateStreak($user, $now);

            // Check daily target bonus (3 readings completed in 1 day)
            $dailyTargetAchieved = false;
            $completedTodayCount = ReadingProgress::where('user_id', $user->id)
                ->whereDate('completed_at', $todayStr)
                ->count();

            $targetCount = (int) config('matcha.gamification.daily_target_count', 3);
            if ($completedTodayCount >= $targetCount) {
                // Check if bonus already awarded today
                $alreadyAwardedBonusToday = ExpLog::where('user_id', $user->id)
                    ->where('source', 'daily_target')
                    ->whereDate('created_at', $todayStr)
                    ->exists();

                if (! $alreadyAwardedBonusToday) {
                    $bonusExp = (int) config('matcha.gamification.daily_target_bonus_exp', 10);
                    $this->awardExp($user, $bonusExp, 'daily_target', null, $now);
                    $expAwarded += $bonusExp;
                    $dailyTargetAchieved = true;
                    $this->checkAndAwardBadge($user, 'daily_target');
                }
            }

            // Check badges
            $unlockedBadge = $this->checkReadingBadges($user);

            return [
                'success' => true,
                'exp_awarded' => $expAwarded,
                'daily_target_achieved' => $dailyTargetAchieved,
                'badge_unlocked' => $unlockedBadge,
            ];
        });
    }

    /**
     * Submit an answer to a reading quiz and award EXP.
     *
     * @return array{is_correct: bool, exp_awarded: int, explanation: ?string, badge_unlocked: ?Badge}
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
                return [
                    'is_correct' => (bool) $progress->quiz_correct,
                    'exp_awarded' => 0,
                    'explanation' => $quiz->explanation,
                    'badge_unlocked' => null,
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
            }

            $unlockedBadge = null;
            if ($isCorrect) {
                $unlockedBadge = $this->checkAndAwardBadge($user, 'first_quiz_correct');
            }

            return [
                'is_correct' => $isCorrect,
                'exp_awarded' => $expAwarded,
                'explanation' => $quiz->explanation,
                'badge_unlocked' => $unlockedBadge,
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
        if ($user->current_streak >= 3) {
            $this->checkAndAwardBadge($user, 'streak_3');
        }
        if ($user->current_streak >= 7) {
            $this->checkAndAwardBadge($user, 'streak_7');
        }
    }

    /**
     * Check reading count badges.
     */
    public function checkReadingBadges(User $user): ?Badge
    {
        $completedCount = ReadingProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        $unlocked = null;
        if ($completedCount >= 1) {
            $unlocked = $this->checkAndAwardBadge($user, 'first_read') ?? $unlocked;
        }
        if ($completedCount >= 10) {
            $unlocked = $this->checkAndAwardBadge($user, 'readings_10') ?? $unlocked;
        }
        if ($completedCount >= 50) {
            $unlocked = $this->checkAndAwardBadge($user, 'readings_50') ?? $unlocked;
        }

        return $unlocked;
    }

    /**
     * Check and award a specific badge if not already earned.
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
}
