<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value'];

    /**
     * The attributes that should be cast.
     * This allows you to store arrays/objects automatically.
     */
    protected $casts = [
        'setting_value' => 'json', 
    ];
}
