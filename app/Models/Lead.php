<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'reg_number',
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
        'google_place_id',
        'lead_gathering_from',
        'others',
        'pass_key',
        'created_by'
    ];

    protected $casts = [
        'contract_end_date' => 'date',
        'others' => 'array',
    ];

    // ---------- Relationships ----------

    public function leadStatus(){
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function attachments()
    {
        return $this->hasMany(LeadAttachment::class);
    }

    public function priorityStatus(){
        return $this->belongsTo(PriorityStatus::class, 'priority_status_id');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
	public function timelines()
	{
		return $this->hasMany(Timeline::class)->latest();
	}
    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }
    public function salesManager()
    {
        return $this->hasOne(LeadAssignment::class)->where('type', 1);
    }

    public function salesExecutive()
    {
        return $this->hasOne(LeadAssignment::class)->where('type', 2);
    }
    public function notes()
    {
        return $this->hasMany(Note::class)->latest();
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function leadStep()
    {
        return $this->belongsTo(LeadStep::class, 'lead_step_id');
    }

    protected static function booted()
    {
        static::creating(function ($lead) {

            do {
                $passKey = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            } while (self::where('pass_key', $passKey)->exists());

            $lead->pass_key = $passKey;
        });
    }
    
    public function rfqQuotes() {
        return $this->hasMany(RfqQuote::class);
    }
}