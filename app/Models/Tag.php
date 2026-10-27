<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tag_user')->withTimestamps();
    }

    public function readings(): BelongsToMany
    {
        return $this->belongsToMany(Reading::class, 'reading_tag')->withTimestamps();
    }
}
