<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'status',
        'reading_minutes',
        'likes_count',
        'comments_count',
        'completions_count',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'reading_minutes' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'completions_count' => 'integer',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'reading_tag')->withTimestamps();
    }

    public function slides(): HasMany
    {
        return $this->hasMany(ReadingSlide::class)->orderBy('position');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(ReadingQuiz::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('progresses')) {
            return $this->progresses->where('user_id', $user->id)->whereNotNull('completed_at')->isNotEmpty();
        }

        return $this->progresses()->where('user_id', $user->id)->whereNotNull('completed_at')->exists();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
