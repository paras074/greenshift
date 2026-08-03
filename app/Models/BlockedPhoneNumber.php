<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedPhoneNumber extends Model
{
    protected $table = 'blocked_phone_numbers';

    protected $fillable = [
        'phone_number',
    ];
}