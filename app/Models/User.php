<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Scopes ───────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // ── Helpers ──────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
	
	public function timelines()
	{
		return $this->hasMany(Timeline::class);
	}
    public function leadAssignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }
    // Inside app/Models/User.php
    public function tasks()
    {
        // Points to the assign_to column in your tasks table
        return $this->hasMany(Task::class, 'assign_to');
    }
}