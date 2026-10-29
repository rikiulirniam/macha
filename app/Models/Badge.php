<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'category',
        'tier',
        'required_count',
    ];

    /**
     * Category title in natural Indonesian.
     */
    public function getCategoryTitleAttribute(): string
    {
        return match ($this->category) {
            'reading' => 'Membaca',
            'streak' => 'Streak Harian',
            'quiz' => 'Kuis Pengetahuan',
            'comment' => 'Diskusi & Komentar',
            'creator' => 'Kreator Bacaan',
            'like_given' => 'Apresiasi Suka',
            'like_received' => 'Popularitas Karya',
            'level' => 'Tingkat Level',
            'daily_target' => 'Target Harian',
            default => 'Pencapaian',
        };
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
