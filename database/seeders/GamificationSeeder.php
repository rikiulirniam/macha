<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\ExpLog;
use App\Models\Like;
use App\Models\Reading;
use App\Models\ReadingProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        $currentWeekKey = Carbon::now('Asia/Jakarta')->isoFormat('GGGG-[W]WW');
        $users = User::all()->keyBy('username');
        $readings = Reading::all();
        $badges = Badge::all()->keyBy('key');

        if ($users->isEmpty() || $readings->isEmpty()) {
            return;
        }

        // Weekly EXP distribution for rich leaderboard
        $weeklyExpScores = [
            'budi' => 180,
            'anisa' => 140,
            'reza' => 100,
            'dinda' => 80,
            'fajar' => 50,
            'siti' => 40,
            'kevin' => 20,
            'maya' => 10,
        ];

        foreach ($weeklyExpScores as $username => $score) {
            if (! isset($users[$username])) {
                continue;
            }
            $user = $users[$username];
            $remaining = $score;

            // Generate realistic exp logs summing to the score
            while ($remaining > 0) {
                $amount = min($remaining, 10);
                $source = ($amount === 10) ? 'reading_complete' : 'quiz_attempt';
                $reading = $readings->random();

                ExpLog::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'source' => $source,
                    'reading_id' => $reading->id,
                    'week_key' => $currentWeekKey,
                    'created_at' => Carbon::now('Asia/Jakarta')->subHours(rand(1, 72)),
                ]);

                $remaining -= $amount;
            }
        }

        // Seed Badges
        $userBadgeMap = [
            'budi' => ['first_read', 'streak_3', 'streak_7', 'readings_10', 'first_quiz_correct', 'first_publish'],
            'anisa' => ['first_read', 'streak_3', 'first_comment', 'first_publish'],
            'reza' => ['first_read', 'streak_3', 'first_quiz_correct'],
            'dinda' => ['first_read', 'streak_3'],
            'fajar' => ['first_read'],
            'siti' => ['first_read'],
            'kevin' => ['first_read'],
        ];

        foreach ($userBadgeMap as $username => $badgeKeys) {
            if (! isset($users[$username])) {
                continue;
            }
            $user = $users[$username];
            $badgeIds = [];
            foreach ($badgeKeys as $bKey) {
                if (isset($badges[$bKey])) {
                    $badgeIds[$badges[$bKey]->id] = ['earned_at' => Carbon::now('Asia/Jakarta')->subDays(rand(1, 10))];
                }
            }
            $user->badges()->syncWithoutDetaching($badgeIds);
        }

        // Seed Reading Progress
        foreach ($users as $user) {
            $sampleReadings = $readings->random(rand(3, 8));
            foreach ($sampleReadings as $r) {
                $isCompleted = rand(0, 1) === 1;
                $hasQuiz = $r->quiz !== null;
                $quizAnswered = $isCompleted && $hasQuiz ? Carbon::now('Asia/Jakarta')->subDays(rand(1, 5)) : null;

                ReadingProgress::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'reading_id' => $r->id,
                    ],
                    [
                        'last_slide' => $isCompleted ? $r->slides->count() : rand(1, max(1, $r->slides->count() - 1)),
                        'completed_at' => $isCompleted ? Carbon::now('Asia/Jakarta')->subDays(rand(0, 5)) : null,
                        'quiz_answered_at' => $quizAnswered,
                        'quiz_correct' => $quizAnswered ? (rand(0, 3) > 0) : null,
                    ]
                );
            }
        }

        // Seed Likes
        foreach ($users as $user) {
            $liked = $readings->random(rand(4, 10));
            foreach ($liked as $r) {
                Like::firstOrCreate([
                    'user_id' => $user->id,
                    'reading_id' => $r->id,
                ]);
            }
        }

        // Seed Comments
        $sampleComments = [
            'Sangat mencerahkan! Langsung saya coba praktekkan di meja kerja hari ini.',
            'Penjelasannya padat dan tidak bertele-tele. Suka sekali dengan format kartu seperti ini.',
            'Poin terakhir sangat relevan dengan kebiasaan saya. Terima kasih sudah berbagi!',
            'Keren banget, membaca micro begini jadi tidak terasa berat dan tetap bergizi ilmunya.',
            'Kuisnya seru dan bikin ingat inti materinya!',
            'Setuju sekali dengan analogi yang dipakai penulis.',
            'Bagus untuk dibaca saat rehat kopi 5 menit.',
        ];

        foreach ($readings->take(12) as $r) {
            $commentCount = rand(1, 3);
            for ($i = 0; $i < $commentCount; $i++) {
                $commentUser = $users->random();
                Comment::create([
                    'user_id' => $commentUser->id,
                    'reading_id' => $r->id,
                    'body' => $sampleComments[array_rand($sampleComments)],
                    'created_at' => Carbon::now('Asia/Jakarta')->subHours(rand(2, 48)),
                ]);
            }
        }
    }
}
