<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'key' => 'first_read',
                'name' => 'Pembaca Pertama',
                'description' => 'Menyelesaikan bacaan pertamamu di Macha.',
                'icon' => 'book-open',
            ],
            [
                'key' => 'streak_3',
                'name' => 'Konsisten',
                'description' => 'Menjaga streak membaca selama 3 hari beruntun.',
                'icon' => 'flame',
            ],
            [
                'key' => 'streak_7',
                'name' => 'Seminggu Penuh',
                'description' => 'Membaca setiap hari selama 7 hari berturut-turut.',
                'icon' => 'award',
            ],
            [
                'key' => 'readings_10',
                'name' => 'Kutu Buku',
                'description' => 'Menyelesaikan total 10 bacaan bermutu.',
                'icon' => 'library',
            ],
            [
                'key' => 'readings_50',
                'name' => 'Maniak Baca',
                'description' => 'Membaca lebih dari 50 bacaan inspiratif.',
                'icon' => 'trophy',
            ],
            [
                'key' => 'first_quiz_correct',
                'name' => 'Si Pintar',
                'description' => 'Menjawab kuis bacaan dengan benar untuk pertama kali.',
                'icon' => 'sparkles',
            ],
            [
                'key' => 'first_comment',
                'name' => 'Komentator',
                'description' => 'Meninggalkan opini atau komentar pertamamu.',
                'icon' => 'message-circle',
            ],
            [
                'key' => 'first_publish',
                'name' => 'Kreator Baru',
                'description' => 'Membuat dan mempublikasikan bacaan pertamamu.',
                'icon' => 'pen-tool',
            ],
            [
                'key' => 'daily_target',
                'name' => 'Pembaca Aktif',
                'description' => 'Membaca dan menghidupkan streak membaca hari ini.',
                'icon' => 'check-circle-2',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(
                ['key' => $badge['key']],
                $badge
            );
        }
    }
}
