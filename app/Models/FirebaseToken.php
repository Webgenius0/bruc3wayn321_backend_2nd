<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirebaseToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
