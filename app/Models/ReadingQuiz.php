<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading_id',
        'question',
        'options',
        'correct_index',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_index' => 'integer',
        ];
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(Reading::class);
    }
}
