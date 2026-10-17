<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'role',
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
        "email_visibility",
        "phone_visibility",
        "can_activate_free_trial",
    ];

    protected $hidden = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];

    protected $appends = [
        'is_subscribed',
        'current_subscription',
    ];

    protected $casts = [
        'email_visibility' => 'boolean',
        'phone_visibility' => 'boolean',
    ];


    // public function getCanActivateFreeTrialAttribute(): bool
    // {
    //     return $this->isEligibleForFreeTrial() && !$this->is_subscribed;
    // }

    public function getCurrentSubscriptionAttribute(): string
    {
        $subscription = $this->activeSubscription;

        if (!$subscription) {
            return 'paid';
        }

        return $subscription->type === 'free' ? 'free' : 'paid';
    }

    //'can_activate_free_trial' => $user->isEligibleForFreeTrial() && !$user->is_subscribed,

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


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getIsSubscribedAttribute(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now())
                    ->orWhere(function ($q) {
                        $q->where('type', 'free')
                            ->whereNull('free_expires_at')
                            ->orWhere('free_expires_at', '>', now());
                    });
            })
            ->exists();
    }

    /**
     * Get the current subscription (paid or free).
     */
    public function getActiveSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('type', '!=', 'free')
                        ->where(function ($inner) {
                            $inner->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                })->orWhere(function ($q) {
                    $q->where('type', 'free')
                        ->where(function ($inner) {
                            $inner->whereNull('free_expires_at')
                                ->orWhere('free_expires_at', '>', now());
                        });
                });
            })
            ->first();
    }

    /**
     * Check if user has a free subscription (freemium or active trial).
     */
    public function hasFreeSubscription(): bool
    {
        return (bool) $this->activeFreeSubscription;
    }

    public function getActiveFreeSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->where('type', 'free')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('free_expires_at')
                    ->orWhere('free_expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if user has a paid subscription via RevenueCat.
     */
    public function hasPaidSubscription(): bool
    {
        return (bool) $this->activePaidSubscription;
    }

    public function getActivePaidSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->where('type', 'revenuecat')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if user can still activate a free trial/subscription (one-time only).
     */
    public function getCanActivateFreeTrialAttribute(): bool
    {
        return (bool) ($this->attributes['can_activate_free_trial'] ?? true);
    }

    /**
     * Check if user has already used their free trial.
     */
    public function hasUsedFreeTrial(): bool
    {
        return (bool) $this->subscriptions()
            ->where('type', 'free')
            ->where('trial_type', 'free_trial')
            ->exists();
    }

    /**
     * Check if user is eligible for a free trial.
     */
    public function isEligibleForFreeTrial(): bool
    {
        return !$this->hasUsedFreeTrial();
    }

    /**
     * Get usage limits for the user's current tier.
     */
    public function getTierLimits(): array
    {
        if ($this->hasPaidSubscription()) {
            return config('subscription.tiers.paid');
        }

        return config('subscription.tiers.free');
    }

    /**
     * Get or create usage record for a specific type and period.
     */
    public function getUsageLimit(string $type, ?string $periodStart = null): UsageLimit
    {
        $period = $periodStart ?? now()->format('Y-m-01'); // Monthly for paid

        return UsageLimit::firstOrCreate(
            [
                'user_id' => $this->id,
                'type' => $type,
                'period_start' => $period,
            ],
            [
                'limit' => $this->getLimitForType($type),
                'current_count' => 0,
            ]
        );
    }

    /**
     * Get the limit value for a specific usage type.
     */
    protected function getLimitForType(string $type): int
    {
        $limits = $this->getTierLimits();

        return match ($type) {
            'ai_chat' => $limits['ai_chat_messages'] ?? 0,
            'ai_voice' => $limits['ai_voice_requests'] ?? 0,
            'posts' => $limits['community_posts'] ?? 0,
            default => 0,
        };
    }

    /**
     * Update existing usage limits based on current tier.
     * Call this when the user upgrades or downgrades.
     */
    public function updateUsageLimits(): void
    {
        $period = now()->format('Y-m-01');
        $types = ['ai_chat', 'ai_voice', 'posts'];

        foreach ($types as $type) {
            UsageLimit::where('user_id', $this->id)
                ->where('type', $type)
                ->where('period_start', $period)
                ->update(['limit' => $this->getLimitForType($type)]);
        }
    }

    /**
     * Check if user can perform an action without exceeding limits.
     */
    public function canPerformAction(string $type): bool
    {
        $limitRecord = $this->getUsageLimit($type);

        // No limit set (unlimited)
        if ($limitRecord->limit >= 99999) {
            return true;
        }

        return $limitRecord->current_count < $limitRecord->limit;
    }

    /**
     * Increment usage count for a specific type.
     */
    public function incrementUsage(string $type): UsageLimit
    {
        $limitRecord = $this->getUsageLimit($type);

        // Don't increment if already at limit
        if ($limitRecord->current_count >= $limitRecord->limit && $limitRecord->limit < 99999) {
            return $limitRecord;
        }

        $limitRecord->current_count = min($limitRecord->current_count + 1, $limitRecord->limit);
        $limitRecord->save();

        return $limitRecord;
    }

    /**
     * Decrement usage count (for refunds/cancellations).
     */
    public function decrementUsage(string $type, int $amount = 1): UsageLimit
    {
        $limitRecord = $this->getUsageLimit($type);
        $limitRecord->current_count = max(0, $limitRecord->current_count - $amount);
        $limitRecord->save();

        return $limitRecord;
    }

    /**
     * Get remaining quota for a specific type.
     */
    public function getRemainingQuota(string $type): int
    {
        $limitRecord = $this->getUsageLimit($type);
        $remaining = $limitRecord->limit - $limitRecord->current_count;

        return max(0, $remaining);
    }

    /**
     * Get all usage statistics for the user.
     */
    public function getUsageStats(): array
    {
        $aiChat = $this->getUsageLimit('ai_chat');
        $aiVoice = $this->getUsageLimit('ai_voice');
        $posts = $this->getUsageLimit('posts');

        return [
            'ai_chat' => [
                'used' => $aiChat->current_count,
                'limit' => $aiChat->limit,
                'remaining' => $this->getRemainingQuota('ai_chat'),
                'percentage_used' => $aiChat->limit > 0
                    ? number_format(($aiChat->current_count / $aiChat->limit) * 100, 2, '.', '')
                    : '0.00',
            ],

            'ai_voice' => [
                'used' => $aiVoice->current_count,
                'limit' => $aiVoice->limit,
                'remaining' => $this->getRemainingQuota('ai_voice'),
                'percentage_used' => $aiVoice->limit > 0
                    ? number_format(($aiVoice->current_count / $aiVoice->limit) * 100, 2, '.', '')
                    : '0.00',
            ],

            'posts' => [
                'used' => $posts->current_count,
                'limit' => $posts->limit,
                'remaining' => $this->getRemainingQuota('posts'),
                'percentage_used' => $posts->limit > 0
                    ? number_format(($posts->current_count / $posts->limit) * 100, 2, '.', '')
                    : '0.00',
            ],
        ];
    }

    public function getEmailAttribute($value)
    {
        if ($this->email_visibility) {
            return $value;
        }

        if (!$value) {
            return '****';
        }

        [$username, $domain] = explode('@', $value, 2);

        $length = strlen($username);

        if ($length <= 2) {
            $maskedUsername = substr($username, 0, 1) . '*';
        } else {
            $maskedUsername =
                substr($username, 0, 1)
                . str_repeat('*', $length - 2)
                . substr($username, -1);
        }

        return $maskedUsername . '@' . $domain;
    }

    public function getPhoneAttribute($value)
    {
        if ($this->phone_visibility) {
            return $value;
        }

        if (!$value) {
            return '****';
        }

        $length = strlen($value);

        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 1)
            . str_repeat('*', $length - 2)
            . substr($value, -1);
    }
}
