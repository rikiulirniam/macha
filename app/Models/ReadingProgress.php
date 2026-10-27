<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    use HasFactory;

    protected $table = 'reading_progress';

    protected $fillable = [
        'user_id',
        'reading_id',
        'last_slide',
        'completed_at',
        'quiz_answered_at',
        'quiz_correct',
    ];

    protected function casts(): array
    {
        return [
            'last_slide' => 'integer',
            'completed_at' => 'datetime',
            'quiz_answered_at' => 'datetime',
            'quiz_correct' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(Reading::class);
    }
}
