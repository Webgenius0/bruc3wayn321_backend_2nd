<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'store',
        'type',
        'product_id',
        'entitlement',
        'transaction_id',
        'original_transaction_id',
        'status',
        'purchase_date',
        'expires_at',
        'free_expires_at',
        'trial_type',
        'will_renew',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'expires_at' => 'datetime',
        'free_expires_at' => 'datetime',
        'will_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this subscription is currently active (free or paid).
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->type === 'free') {
            return $this->free_expires_at === null || $this->free_expires_at->isFuture();
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Check if this is a free subscription (freemium or trial).
     */
    public function isFree(): bool
    {
        return $this->type === 'free';
    }

    /**
     * Check if this is a paid RevenueCat subscription.
     */
    public function isPaid(): bool
    {
        return $this->type === 'revenuecat';
    }

    /**
     * Time remaining until expiration (free or paid).
     */
    public function getRemainingTime(): ?\Carbon\Carbon
    {
        $expiresAt = $this->type === 'free' ? $this->free_expires_at : $this->expires_at;
        if ($expiresAt === null) {
            return null; // No expiry
        }
        return $expiresAt->gt(now()) ? $expiresAt : null;
    }
}
