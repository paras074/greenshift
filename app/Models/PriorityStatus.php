<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriorityStatus extends Model
{
    protected $fillable = ['name', 'color', 'status', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}