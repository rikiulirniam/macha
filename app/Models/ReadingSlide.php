<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'reading_id',
        'position',
        'type', // text, quote, image, audio
        'body',
        'source',
        'media_path',
        'bg_color', // teal, kilat, amber, paper
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(Reading::class);
    }
}
