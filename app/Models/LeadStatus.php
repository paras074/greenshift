<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    protected $fillable = ['name', 'color', 'status', 'sort_order', 'show_kanban'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function leads() {
        return $this->hasMany(Lead::class, 'lead_status_id');
    }
}