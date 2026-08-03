<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAttachment extends Model
{
    protected $fillable = [
        'lead_id', 'uploaded_by', 'file_path', 
        'file_name', 'file_type', 'file_size', 'description', 'others'
    ];

    protected $casts = [
        'others' => 'array',
    ];

    // Accessor for human-readable size (e.g., 1.5 MB)
    public function getReadableSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function lead() {
        return $this->belongsTo(Lead::class);
    }

    public function uploader() {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}