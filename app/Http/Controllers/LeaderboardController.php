<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentWeekKey = $now->isoFormat('GGGG-[W]WW');

        // Aggregated query with index on (week_key, user_id)
        $topUsers = DB::table('exp_logs')
            ->join('users', 'exp_logs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.current_streak',
                'users.total_exp',
                DB::raw('SUM(exp_logs.amount) as weekly_exp')
            )
            ->where('exp_logs.week_key', $currentWeekKey)
            ->groupBy('users.id', 'users.name', 'users.username', 'users.current_streak', 'users.total_exp')
            ->having('weekly_exp', '>', 0)
            ->orderByDesc('weekly_exp')
            ->limit(20)
            ->get();

        // Calculate initials for each user
        $topUsers = $topUsers->map(function ($u, $idx) {
            $words = explode(' ', trim($u->name));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $w) {
                $initials .= mb_substr($w, 0, 1);
            }
            $u->initials = mb_strtoupper($initials ?: 'M');
            $u->rank = $idx + 1;

            return $u;
        });

        // Current user position & weekly exp
        $currentUser = $request->user();
        $currentUserRank = null;
        $currentUserWeeklyExp = 0;

        if ($currentUser) {
            $userScore = DB::table('exp_logs')
                ->where('user_id', $currentUser->id)
                ->where('week_key', $currentWeekKey)
                ->sum('amount');

            $currentUserWeeklyExp = (int) $userScore;

            if ($currentUserWeeklyExp > 0) {
                // Rank calculation
                $higherCount = DB::table('exp_logs')
                    ->select('user_id', DB::raw('SUM(amount) as total'))
                    ->where('week_key', $currentWeekKey)
                    ->groupBy('user_id')
                    ->having('total', '>', $currentUserWeeklyExp)
                    ->get()
                    ->count();

                $currentUserRank = $higherCount + 1;
            }
        }

        // Countdown to next Monday 00:00 WIB
        $nextMonday = $now->copy()->next(Carbon::MONDAY)->startOfDay();
        $totalMinutes = (int) floor($now->diffInMinutes($nextMonday));
        $totalHours = intdiv($totalMinutes, 60);
        $daysUntilReset = intdiv($totalHours, 24);
        $hoursUntilReset = $totalHours % 24;

        if ($daysUntilReset > 0) {
            $countdown = "{$daysUntilReset} hari {$hoursUntilReset} jam";
        } elseif ($hoursUntilReset > 0) {
            $countdown = "{$hoursUntilReset} jam";
        } else {
            $minutes = max(1, $totalMinutes % 60);
            $countdown = "{$minutes} menit";
        }

        return view('leaderboard', [
            'topUsers' => $topUsers,
            'podium' => $topUsers->take(3),
            'remainingList' => $topUsers->slice(3),
            'currentUser' => $currentUser,
            'currentUserRank' => $currentUserRank,
            'currentUserWeeklyExp' => $currentUserWeeklyExp,
            'countdown' => $countdown,
        ]);
    }
}
