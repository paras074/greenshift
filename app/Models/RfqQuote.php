<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqQuote extends Model
{
    protected $table = 'rfq_quotes'; 

    protected $fillable = [
        'title',
        'lead_id',
        'supplier_name',
        'phone',
        'email',
        'delivery_timeline',
        'price',
        'warranty',
        'description',
        'others',
    ];

    // Cast 'others' to an array automatically when reading/writing to DB
    protected $casts = [
        'others' => 'array',
    ];
}