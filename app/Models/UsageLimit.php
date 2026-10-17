<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageLimit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'period_start',
        'current_count',
        'limit',
    ];

    protected $casts = [
        'period_start' => 'date',
        'current_count' => 'integer',
        'limit' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
