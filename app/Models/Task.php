<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Lead;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'type', 'lead_id', 
        'assign_to', 'assign_by', 'end_date', 
        'priority', 'status', 'others'
    ];

    protected $casts = [
        'end_date'  => 'date',
        'assign_to' => 'array',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    public function assignedUsers()
    {
        return User::whereIn('id', $this->assign_to ?? [])->get();
    }

    // Mutators to force lowercase
    protected function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtolower($value);
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class);
    }

    protected function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }
    public function latestNote()
    {
        return $this->hasOne(TaskNote::class)->latestOfMany();
    }
}