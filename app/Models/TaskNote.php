<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'data',
        'mentioned_id',
        'others',
    ];

    /**
     * Get the task. Returns null if the task was deleted.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class)->withDefault();
    }

    /**
     * Get the creator. Returns null if the user was deleted.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User'
        ]);
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_id');
    }
}