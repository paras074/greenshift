<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStep extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'status'
    ];

    // Optional: Cast status to boolean if you prefer working with true/false
    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
    ];
}