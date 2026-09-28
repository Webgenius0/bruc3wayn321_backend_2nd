<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'country_code',
        'flag_url',
        'is_active'
    ];
}
