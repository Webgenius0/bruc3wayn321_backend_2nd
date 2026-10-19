<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'slug',
        'content',
        'location',
        "latitude",
        "longitude",
        'images',
        'audios',
        'videos',
        'visibility',
        'is_draft',
        'is_published',
    ];

    protected $casts = [
        'images'        => 'array',
        'audios'        => 'array',
        'videos'        => 'array',
        'visibility'    => 'boolean',
        'is_draft'      => 'boolean',
        'is_published'  => 'boolean',
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

    public function publisher(): BelongsTo
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


    public function scopeTrending($query)
    {
        return $query
            ->withCount([
                'likes',
                'comments',
                'views'
            ])
            ->orderByDesc('likes_count')
            ->orderByDesc('comments_count')
            ->orderByDesc('views_count');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }
}
