<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'avatar_path',
    'total_exp',
    'current_streak',
    'longest_streak',
    'last_read_date',
    'onboarded_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_read_date' => 'date',
            'onboarded_at' => 'datetime',
            'total_exp' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_user')->withTimestamps();
    }

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    public function readingProgresses(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likedReadings(): BelongsToMany
    {
        return $this->belongsToMany(Reading::class, 'likes')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function expLogs(): HasMany
    {
        return $this->hasMany(ExpLog::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Calculate user level based on total EXP.
     */
    public function getLevelAttribute(): int
    {
        return (int) (floor($this->total_exp / 100) + 1);
    }

    /**
     * EXP progress within current level (0-99).
     */
    public function getLevelProgressAttribute(): int
    {
        return $this->total_exp % 100;
    }

    /**
     * Get user initials for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }

        return mb_strtoupper($initials ?: 'M');
    }

    /**
     * Weekly EXP total for a specific week_key (default current week).
     */
    public function getWeeklyExp(?string $weekKey = null): int
    {
        $weekKey ??= Carbon::now('Asia/Jakarta')->isoFormat('GGGG-[W]WW');

        return (int) $this->expLogs()->where('week_key', $weekKey)->sum('amount');
    }
}
