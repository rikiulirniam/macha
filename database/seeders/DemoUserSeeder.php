<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $usersData = [
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 350,
                'current_streak' => 7,
                'longest_streak' => 12,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Anisa Rahma',
                'username' => 'anisa',
                'email' => 'anisa@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 280,
                'current_streak' => 5,
                'longest_streak' => 8,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Reza Pratama',
                'username' => 'reza',
                'email' => 'reza@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 210,
                'current_streak' => 3,
                'longest_streak' => 6,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Dinda Ayu',
                'username' => 'dinda',
                'email' => 'dinda@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 170,
                'current_streak' => 4,
                'longest_streak' => 5,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Fajar Nugroho',
                'username' => 'fajar',
                'email' => 'fajar@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 120,
                'current_streak' => 2,
                'longest_streak' => 4,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'username' => 'siti',
                'email' => 'siti@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 90,
                'current_streak' => 1,
                'longest_streak' => 3,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Kevin Sanjaya',
                'username' => 'kevin',
                'email' => 'kevin@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 60,
                'current_streak' => 1,
                'longest_streak' => 2,
                'last_read_date' => $today,
                'onboarded_at' => now(),
            ],
            [
                'name' => 'Maya Putri',
                'username' => 'maya',
                'email' => 'maya@macha.id',
                'password' => Hash::make('password'),
                'total_exp' => 30,
                'current_streak' => 0,
                'longest_streak' => 1,
                'last_read_date' => null,
                'onboarded_at' => now(),
            ],
        ];

        $tags = Tag::all();

        foreach ($usersData as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign 3-4 random tags to each user for onboarding simulation
            if ($tags->isNotEmpty()) {
                $user->tags()->syncWithoutDetaching($tags->random(rand(3, 4))->pluck('id'));
            }
        }
    }
}
