<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignment extends Model
{
    protected $table = 'lead_assignment'; // Explicitly set if table is singular

    protected $fillable = ['lead_id', 'user_id'];

    // Get the lead associated with this assignment
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Get the user associated with this assignment
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}