<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GamificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GamificationService;
    }

    public function test_week_key_generation(): void
    {
        // 2026-10-28 is Wednesday in Week 44
        $date = Carbon::create(2026, 10, 28, 12, 0, 0, 'Asia/Jakarta');
        $weekKey = $this->service->getWeekKey($date);

        $this->assertSame('2026-W44', $weekKey);

        // Year turnover: 2026-12-31 vs 2027-01-01
        $endOfYear = Carbon::create(2026, 12, 31, 10, 0, 0, 'Asia/Jakarta');
        $weekKeyEnd = $this->service->getWeekKey($endOfYear);
        $this->assertSame('2026-W53', $weekKeyEnd);

        $startOfYear = Carbon::create(2027, 1, 4, 10, 0, 0, 'Asia/Jakarta');
        $weekKeyStart = $this->service->getWeekKey($startOfYear);
        $this->assertSame('2027-W01', $weekKeyStart);
    }

    public function test_level_calculation(): void
    {
        $this->assertSame(1, $this->service->calculateLevel(0));
        $this->assertSame(1, $this->service->calculateLevel(50));
        $this->assertSame(1, $this->service->calculateLevel(99));
        $this->assertSame(2, $this->service->calculateLevel(100));
        $this->assertSame(3, $this->service->calculateLevel(250));
        $this->assertSame(11, $this->service->calculateLevel(1050));
    }

    public function test_streak_starts_at_one_for_first_reading(): void
    {
        $user = User::factory()->create([
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_read_date' => null,
        ]);

        $this->service->updateStreak($user, Carbon::create(2026, 10, 26, 10, 0, 0, 'Asia/Jakarta'));

        $user->refresh();
        $this->assertSame(1, $user->current_streak);
        $this->assertSame(1, $user->longest_streak);
        $this->assertSame('2026-10-26', $user->last_read_date->toDateString());
    }

    public function test_streak_remains_the_same_if_reading_again_on_same_day(): void
    {
        $user = User::factory()->create([
            'current_streak' => 3,
            'longest_streak' => 5,
            'last_read_date' => '2026-10-26',
        ]);

        $this->service->updateStreak($user, Carbon::create(2026, 10, 26, 18, 0, 0, 'Asia/Jakarta'));

        $user->refresh();
        $this->assertSame(3, $user->current_streak);
        $this->assertSame(5, $user->longest_streak);
    }

    public function test_streak_increments_on_consecutive_day(): void
    {
        $user = User::factory()->create([
            'current_streak' => 3,
            'longest_streak' => 3,
            'last_read_date' => '2026-10-25',
        ]);

        $this->service->updateStreak($user, Carbon::create(2026, 10, 26, 8, 0, 0, 'Asia/Jakarta'));

        $user->refresh();
        $this->assertSame(4, $user->current_streak);
        $this->assertSame(4, $user->longest_streak);
        $this->assertSame('2026-10-26', $user->last_read_date->toDateString());
    }

    public function test_streak_resets_to_one_if_more_than_one_day_is_missed(): void
    {
        $user = User::factory()->create([
            'current_streak' => 7,
            'longest_streak' => 10,
            'last_read_date' => '2026-10-20',
        ]);

        // Reading on 2026-10-26 (missed 5 days)
        $this->service->updateStreak($user, Carbon::create(2026, 10, 26, 8, 0, 0, 'Asia/Jakarta'));

        $user->refresh();
        $this->assertSame(1, $user->current_streak);
        $this->assertSame(10, $user->longest_streak); // longest streak preserved
        $this->assertSame('2026-10-26', $user->last_read_date->toDateString());
    }
}
