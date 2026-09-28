<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $fillable = [
        "post_id",
        "user_id",
        "comment",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
