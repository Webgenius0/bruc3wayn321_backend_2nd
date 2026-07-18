<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, HasApiTokens;

    protected $fillable = [
        'name',
        "first_name",
        "last_name",
        "bio",
        "user_name",
        'email',
        'password',
        'avatar',
        'cover_image',
        // 'role',
        'email_verified_at',
        'remember_token',
        'phone',
        'date_of_birth',
        'currency',
        'currency_flug',

        "email_notifications",
        "push_notifications",
        "sms_notifications",
        "match_notifications",
        "message_notifications",
        "like_notifications",
        "marketing_notifications",
        "status",
    ];

    protected $hidden = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];


    public function destinations()
    {
        return $this->hasMany(UserDestination::class);
    }

    public function posts()
    {
        return $this->hasMany(\App\Models\Post::class);
    }

    /**
     * Get all AI concierge conversations for this user.
     */
    public function aiConversations()
    {
        return $this->hasMany(AiConversation::class);
    }

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
            'two_factor_confirmed_at' => 'datetime',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'match_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'like_notifications' => 'boolean',
            'marketing_notifications' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (request()->is('api/*')) {
            return asset($value);
        }
        return $value;
    }
    public function getCoverImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (request()->is('api/*')) {
            return asset($value);
        }
        return $value;
    }

    /**
     * Get all followers of this user
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    /**
     * Get all users this user is following
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    /**
     * Check if this user is followed by another user
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Check if this user is following another user
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Get followers count
     */
    public function getFollowersCount(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get following count
     */
    public function getFollowingCount(): int
    {
        return $this->following()->count();
    }
}
