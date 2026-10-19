<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;
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
