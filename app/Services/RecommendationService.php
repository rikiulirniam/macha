<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Get personalized recommended readings for a user.
     *
     * @return \Illuminate\Support\Collection<int, Reading>
     */
    public function getRecommendedReadings(?User $user, int $limit = 10, int $offset = 0, ?int $tagId = null): \Illuminate\Support\Collection
    {
        $query = Reading::published()
            ->with(['user', 'tags', 'slides', 'quiz', 'likes', 'progresses']);

        if ($tagId) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
        }

        $allReadings = $query->get();

        if (! $user) {
            // Guest fallback: rank by popularity and freshness + jitter
            return $allReadings->map(function (Reading $reading) {
                $score = log10(1 + $reading->likes_count);
                if ($reading->published_at && $reading->published_at->gt(now()->subDays(3))) {
                    $score += 2.0;
                }
                $jitter = (mt_rand(-100, 100) / 100.0);
                $reading->recommendation_score = $score + $jitter;

                return $reading;
            })
                ->sortByDesc('recommendation_score')
                ->values()
                ->slice($offset, $limit);
        }

        // Preload user data for scoring
        $userTagIds = $user->tags()->pluck('tags.id')->flip()->toArray();

        // Tags from readings user liked in the last 30 days
        $likedReadingTagIds = DB::table('likes')
            ->join('reading_tag', 'likes.reading_id', '=', 'reading_tag.reading_id')
            ->where('likes.user_id', $user->id)
            ->where('likes.created_at', '>=', Carbon::now('Asia/Jakarta')->subDays(30))
            ->pluck('reading_tag.tag_id')
            ->flip()
            ->toArray();

        // Creator IDs whose content the user has liked
        $likedCreatorIds = DB::table('likes')
            ->join('readings', 'likes.reading_id', '=', 'readings.id')
            ->where('likes.user_id', $user->id)
            ->pluck('readings.user_id')
            ->flip()
            ->toArray();

        // Completed reading IDs by the user
        $completedReadingIds = DB::table('reading_progress')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('reading_id')
            ->flip()
            ->toArray();

        $threeDaysAgo = Carbon::now('Asia/Jakarta')->subDays(3);

        $scoredReadings = $allReadings->map(function (Reading $reading) use (
            $userTagIds,
            $likedReadingTagIds,
            $likedCreatorIds,
            $completedReadingIds,
            $threeDaysAgo
        ) {
            $score = 0.0;
            $hasMatchingInterest = false;

            // +5 per tag matching onboarding tags
            foreach ($reading->tags as $tag) {
                if (isset($userTagIds[$tag->id])) {
                    $score += 5.0;
                    $hasMatchingInterest = true;
                }
                // +3 per tag matching tags liked in last 30 days
                if (isset($likedReadingTagIds[$tag->id])) {
                    $score += 3.0;
                    $hasMatchingInterest = true;
                }
            }

            // +2 if creator was previously liked by user
            if (isset($likedCreatorIds[$reading->user_id])) {
                $score += 2.0;
            }

            // +2 if published < 3 days ago
            if ($reading->published_at && $reading->published_at->gt($threeDaysAgo)) {
                $score += 2.0;
            }

            // +1 * log10(1 + likes_count)
            $score += (1.0 * log10(1 + max(0, $reading->likes_count)));

            // Jitter ±1
            $jitter = (mt_rand(-100, 100) / 100.0);
            $score += $jitter;

            // If already completed by user, multiply score by 0.1 (deprioritize)
            if (isset($completedReadingIds[$reading->id])) {
                $score *= 0.1;
            }

            $reading->recommendation_score = $score;
            $reading->has_matching_interest = $hasMatchingInterest;

            return $reading;
        });

        // Separate matching readings and outside interest readings for exploration insertion
        $interestReadings = $scoredReadings->filter(fn ($r) => $r->has_matching_interest)->sortByDesc('recommendation_score')->values();
        $explorationReadings = $scoredReadings->filter(fn ($r) => ! $r->has_matching_interest)->sortByDesc('recommendation_score')->values();

        // Blend exploration: every 5th item insert 1 reading outside user's interests
        $result = collect();
        $interestIndex = 0;
        $explorationIndex = 0;
        $totalItems = $scoredReadings->count();

        for ($i = 0; $i < $totalItems; $i++) {
            if (($i + 1) % 5 === 0 && $explorationIndex < $explorationReadings->count()) {
                $result->push($explorationReadings[$explorationIndex++]);
            } elseif ($interestIndex < $interestReadings->count()) {
                $result->push($interestReadings[$interestIndex++]);
            } elseif ($explorationIndex < $explorationReadings->count()) {
                $result->push($explorationReadings[$explorationIndex++]);
            }
        }

        return $result->slice($offset, $limit)->values();
    }

    /**
     * Get latest readings ordered by published_at desc.
     *
     * @return \Illuminate\Support\Collection<int, Reading>
     */
    public function getLatestReadings(int $limit = 10, int $offset = 0, ?int $tagId = null): \Illuminate\Support\Collection
    {
        $query = Reading::published()
            ->with(['user', 'tags', 'slides', 'quiz', 'likes', 'progresses'])
            ->latest('published_at');

        if ($tagId) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
        }

        return $query->skip($offset)->take($limit)->get();
    }
}
