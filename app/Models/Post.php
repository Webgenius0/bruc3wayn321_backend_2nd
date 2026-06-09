<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'content',
        'location',
        'images',
        'audios',
        'videos',
        'is_draft',
        'is_published',
    ];

    protected $casts = [
        'images' => 'array',
        'audios' => 'array',
        'videos' => 'array',
        'is_draft' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * Get the route key name for Laravel route model binding.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relationship to the author user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to order by latest created.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by trending (placeholder uses updated_at).
     */
    public function scopeTrending($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

}
