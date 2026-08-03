<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $fillable = ['name', 'icon', 'status', 'sort_order'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}