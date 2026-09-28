<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenuecatEvent extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
