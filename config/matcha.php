<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Macha Application Configuration
    |--------------------------------------------------------------------------
    */

    'app_name' => env('APP_NAME', 'Macha'),

    'gamification' => [
        'reading_complete_exp' => 10,
        'quiz_attempt_exp' => 5,
        'quiz_correct_bonus_exp' => 5, // total 10 for correct answers
        'daily_target_bonus_exp' => 0,
        'daily_target_count' => 1,
        'level_divisor' => 100, // Level = floor(total_exp / 100) + 1
    ],

    /*
    |--------------------------------------------------------------------------
    | Banned Words for Comments Moderation
    |--------------------------------------------------------------------------
    | Sederhana namun efektif untuk memfilter kata-kata kasar / toksik
    | dengan pesan penolakan yang ramah dan mendidik.
    */
    'banned_words' => [
        'anjing',
        'babi',
        'bangsat',
        'kontol',
        'memek',
        'pantek',
        'goblok',
        'tolol',
        'idiot',
        'bajingan',
        'tai',
        'ngentot',
        'kampret',
        'bodoh',
        'perek',
        'itil',
        'asu',
        'fuck',
        'shit',
        'asshole',
        'bitch',
    ],
];
