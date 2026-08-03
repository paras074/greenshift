<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'lead_id',
        'type',
        'kind',
        'from',
        'to',
        'is_read',
        'message',
        'url',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Related lead
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    // Only unread
    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }

    // For specific user
    public function scopeForUser($query, $userId)
    {
        return $query->where('to', $userId);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['is_read' => 1]);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'to');
    }
}