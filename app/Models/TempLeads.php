<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TempLeads extends Model
{
    use HasFactory;

    protected $table = 'temp_leads';
    protected $fillable = [
        'company_name',
        'google_place_id',
        'email',
        'phone',
        'annual_consumption',
        'total_annual_consumption',
        'budget_range',
        'roof_site_type',
        'decision_maker_name',
        'mpan',
        'address',
        'city',
        'state',
        'postcode',
        'description',
        'current_supplier',
        'energy_type',
        'lead_step_id',
        'aq',
        'lead_status_id',
        'contract_end_date',
        'priority_status_id',
        'status',
        'created_by',
        'lead_gathering_from',
        'others',
        'notes',
    ];

    protected $casts = [
        'others' => 'array',
        'notes'  => 'array',
    ];
}