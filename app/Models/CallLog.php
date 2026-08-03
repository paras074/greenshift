<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $table = 'call_logs';

    protected $fillable = [
        'dialpad_call_id',
        'lead_id',
        'user_id',
        'contact_number',
        'direction',
        'state',
        'agent_name',
        'agent_email',
        'call_started_at',
        'call_ended_at',
        'duration',
        'local_recording_path',
        'others'
    ];

    /**
     * Automatic Type Casting configuration settings.
     */
    protected function casts(): array
    {
        return [
            'others'          => 'array', // Automatically casts database JSON string records to native PHP arrays
            'call_started_at' => 'datetime',
            'call_ended_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}