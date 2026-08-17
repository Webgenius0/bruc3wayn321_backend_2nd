<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'store',
        'product_id',
        'entitlement',
        'transaction_id',
        'original_transaction_id',
        'status',
        'purchase_date',
        'expires_at',
        'will_renew',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'expires_at' => 'datetime',
        'will_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
