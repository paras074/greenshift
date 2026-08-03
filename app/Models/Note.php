<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    // Allows these fields to be filled via Note::create()
    protected $fillable = [
        'lead_id',
        'user_id',
        'data',
        'mentioned_id',
        'others'
    ];

    // Relationship: The lead this note belongs to
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Relationship: The user who wrote the note
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: The user mentioned in the note
    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_id');
    }
}