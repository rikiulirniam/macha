<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Teknologi',
            'Pengembangan Diri',
            'Sains',
            'Sejarah',
            'Bisnis',
            'Kesehatan',
            'Sastra',
            'Psikologi',
            'Keuangan',
            'Lingkungan',
            'Bahasa',
        ];

        foreach ($tags as $name) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
