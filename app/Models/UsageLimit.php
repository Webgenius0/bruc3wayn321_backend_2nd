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
        'current_count' => 'integer',
        'limit' => 'integer',
    ];

    public function setPeriodStartAttribute($value)
    {
        $this->attributes['period_start'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
