<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // Kategori 1: Membaca (Reading)
            [
                'key' => 'first_read',
                'name' => 'Pembaca Pertama',
                'description' => 'Menyelesaikan bacaan pertamamu di Macha.',
                'icon' => 'book-open',
                'category' => 'reading',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'readings_10',
                'name' => 'Kutu Buku',
                'description' => 'Menyelesaikan total 10 bacaan bermutu.',
                'icon' => 'library',
                'category' => 'reading',
                'tier' => 2,
                'required_count' => 10,
            ],
            [
                'key' => 'readings_50',
                'name' => 'Maniak Baca',
                'description' => 'Membaca lebih dari 50 bacaan inspiratif.',
                'icon' => 'trophy',
                'category' => 'reading',
                'tier' => 3,
                'required_count' => 50,
            ],
            [
                'key' => 'readings_100',
                'name' => 'Kolektor Ilmu',
                'description' => 'Menyelesaikan 100 bacaan dan menjadi maestro literasi.',
                'icon' => 'book-marked',
                'category' => 'reading',
                'tier' => 4,
                'required_count' => 100,
            ],

            // Kategori 2: Streak Membaca (Streak)
            [
                'key' => 'streak_3',
                'name' => 'Konsisten',
                'description' => 'Menjaga streak membaca selama 3 hari beruntun.',
                'icon' => 'flame',
                'category' => 'streak',
                'tier' => 1,
                'required_count' => 3,
            ],
            [
                'key' => 'streak_7',
                'name' => 'Seminggu Penuh',
                'description' => 'Membaca setiap hari selama 7 hari berturut-turut.',
                'icon' => 'award',
                'category' => 'streak',
                'tier' => 2,
                'required_count' => 7,
            ],
            [
                'key' => 'streak_30',
                'name' => 'Disiplin Baja',
                'description' => 'Membaca setiap hari selama 30 hari berturut-turut.',
                'icon' => 'zap',
                'category' => 'streak',
                'tier' => 3,
                'required_count' => 30,
            ],

            // Kategori 3: Kuis Pengetahuan (Quiz)
            [
                'key' => 'first_quiz_correct',
                'name' => 'Si Pintar',
                'description' => 'Menjawab kuis bacaan dengan benar untuk pertama kali.',
                'icon' => 'sparkles',
                'category' => 'quiz',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'quiz_5',
                'name' => 'Pemikir Kritis',
                'description' => 'Menjawab 5 kuis bacaan dengan benar.',
                'icon' => 'help-circle',
                'category' => 'quiz',
                'tier' => 2,
                'required_count' => 5,
            ],
            [
                'key' => 'quiz_20',
                'name' => 'Cendekiawan',
                'description' => 'Menjawab 20 kuis bacaan dengan benar.',
                'icon' => 'brain',
                'category' => 'quiz',
                'tier' => 3,
                'required_count' => 20,
            ],

            // Kategori 4: Diskusi & Komentar (Comment)
            [
                'key' => 'first_comment',
                'name' => 'Komentator',
                'description' => 'Meninggalkan opini atau komentar pertamamu.',
                'icon' => 'message-circle',
                'category' => 'comment',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'comments_5',
                'name' => 'Pengulas Aktif',
                'description' => 'Meninggalkan 5 komentar dalam diskusi bacaan.',
                'icon' => 'messages-square',
                'category' => 'comment',
                'tier' => 2,
                'required_count' => 5,
            ],
            [
                'key' => 'comments_25',
                'name' => 'Jawara Diskusi',
                'description' => 'Meninggalkan 25 komentar dalam berbagai diskusi bacaan.',
                'icon' => 'message-square-quote',
                'category' => 'comment',
                'tier' => 3,
                'required_count' => 25,
            ],

            // Kategori 5: Kreator Bacaan (Creator)
            [
                'key' => 'first_publish',
                'name' => 'Kreator Baru',
                'description' => 'Membuat dan mempublikasikan bacaan pertamamu.',
                'icon' => 'pen-tool',
                'category' => 'creator',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'creator_5',
                'name' => 'Penulis Produktif',
                'description' => 'Menerbitkan 5 karya bacaan untuk komunitas Macha.',
                'icon' => 'feather',
                'category' => 'creator',
                'tier' => 2,
                'required_count' => 5,
            ],
            [
                'key' => 'creator_15',
                'name' => 'Pujangga Macha',
                'description' => 'Menerbitkan 15 karya bacaan bermutu.',
                'icon' => 'scroll',
                'category' => 'creator',
                'tier' => 3,
                'required_count' => 15,
            ],

            // Kategori 6: Apresiasi Suka Diberikan (Like Given)
            [
                'key' => 'first_like',
                'name' => 'Penyemangat',
                'description' => 'Memberikan apresiasi suka pertamamu pada bacaan orang lain.',
                'icon' => 'heart',
                'category' => 'like_given',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'likes_10',
                'name' => 'Apresiator Sejati',
                'description' => 'Menyukai 10 bacaan bermutu karya kreator lain.',
                'icon' => 'thumbs-up',
                'category' => 'like_given',
                'tier' => 2,
                'required_count' => 10,
            ],
            [
                'key' => 'likes_50',
                'name' => 'Sahabat Literasi',
                'description' => 'Menyukai 50 bacaan untuk mendukung para kreator.',
                'icon' => 'smile',
                'category' => 'like_given',
                'tier' => 3,
                'required_count' => 50,
            ],

            // Kategori 7: Popularitas Suka Diterima (Like Received)
            [
                'key' => 'likes_received_1',
                'name' => 'Disukai Pembaca',
                'description' => 'Menerima suka pertamamu dari pembaca karya-karyamu.',
                'icon' => 'star',
                'category' => 'like_received',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'likes_received_10',
                'name' => 'Karya Favorit',
                'description' => 'Mengumpulkan total 10 suka pada karya-karya bacaanmu.',
                'icon' => 'sparkles',
                'category' => 'like_received',
                'tier' => 2,
                'required_count' => 10,
            ],
            [
                'key' => 'likes_received_50',
                'name' => 'Bintang Macha',
                'description' => 'Mengumpulkan total 50 suka pada karya-karya bacaanmu.',
                'icon' => 'crown',
                'category' => 'like_received',
                'tier' => 3,
                'required_count' => 50,
            ],

            // Kategori 8: Tingkat Level Pengguna (Level)
            [
                'key' => 'level_2',
                'name' => 'Pemula Berbakat',
                'description' => 'Mencapai Level 2 dengan aktif membaca dan berdiskusi.',
                'icon' => 'compass',
                'category' => 'level',
                'tier' => 1,
                'required_count' => 2,
            ],
            [
                'key' => 'level_5',
                'name' => 'Penjelajah Ahli',
                'description' => 'Mencapai Level 5 di komunitas Macha.',
                'icon' => 'shield-check',
                'category' => 'level',
                'tier' => 2,
                'required_count' => 5,
            ],
            [
                'key' => 'level_10',
                'name' => 'Legenda Macha',
                'description' => 'Mencapai Level 10 sebagai pembaca ulung.',
                'icon' => 'gem',
                'category' => 'level',
                'tier' => 3,
                'required_count' => 10,
            ],

            // Kategori 9: Target Harian (Daily Target)
            [
                'key' => 'daily_target',
                'name' => 'Pembaca Aktif',
                'description' => 'Membaca dan mencapai target harian pertamamu.',
                'icon' => 'check-circle-2',
                'category' => 'daily_target',
                'tier' => 1,
                'required_count' => 1,
            ],
            [
                'key' => 'daily_target_7',
                'name' => 'Rutin Membaca',
                'description' => 'Menyelesaikan target membaca harian selama 7 hari berbeda.',
                'icon' => 'calendar',
                'category' => 'daily_target',
                'tier' => 2,
                'required_count' => 7,
            ],
            [
                'key' => 'daily_target_30',
                'name' => 'Gaya Hidup Literasi',
                'description' => 'Menyelesaikan target membaca harian selama 30 hari berbeda.',
                'icon' => 'sun',
                'category' => 'daily_target',
                'tier' => 3,
                'required_count' => 30,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['key' => $badge['key']],
                $badge
            );
        }
    }
}
