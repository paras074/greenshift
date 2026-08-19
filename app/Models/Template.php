<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';

    protected $fillable = [
        'name',
        'type',
        'subject',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Return the currently active template for a given type (loa|email).
     */
    public static function activeFor(string $type): ?self
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->first();
    }
}
