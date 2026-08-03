<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'task_id',
        'data',
        'other',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function getFormattedDescriptionAttribute()
    {
        $type = $this->other;
    
        $companyName = $this->lead->company_name ?? 'Unknown Lead';
        $companyId = $this->lead->id ?? '';
        $userName = $this->user->name ?? 'System';
        $taskTitle = $this->task->title ?? 'Deleted Task';
    
        // Wrap entities in spans for custom styling
        $company = "<span class='act-highlight act-highlight--lead'>{$companyName}</span>";
        $user = "<span class='act-highlight act-highlight--user'>{$userName}</span>";
        $task = "<span class='act-highlight act-highlight--task'>\"{$taskTitle}\"</span>";
    
        switch ($type) {
            // --- Lead Events ---
            case 'lead_created':
                return "{$user} created a new lead for {$company}";
    
            case 'lead_updated':
                return "{$user} modified the profile details of {$company}";
    
            case 'lead_deleted':
                return "Lead #{$companyId} was permanently removed by {$user}";
    
            case 'lead_status_updated':
                return "{$user} updated the status for {$company}";
    
            // --- Member Events ---
            case 'member_assigned':
                return "{$user} assigned a team member to {$company}";
    
            case 'member_assigned_bulk':
                return "{$user} assigned team members to {$company}";
    
            case 'admin_loa_generated':
                return "{$user} generated a new LOA for {$company}";
    
            case 'member_unassigned':
                return "{$user} removed a team member from {$company}";
    
            // --- Attachment Events ---
            case 'attachment_added':
                return "{$user} uploaded a new attachment to {$company}";

            case 'customer_added_attachments':
                return "Customer uploaded new attachment(s) to {$company}";

            case 'customer_verified_signed_loa':
                return "Customer verified and signed the LOA for {$company}";
                
            case 'signed_loa_customer':
                return "Customer signed the LOA for <b>{$companyName}</b>.";

            case 'attachment_deleted':
                return "{$user} removed an attachment from {$company}";
    
            // --- Task Events ---
            case 'task_created':
                return "{$user} created task {$task} for {$company}";
    
            case 'task_updated':
                return "{$user} updated the details for task {$task}";
    
            case 'task_deleted':
                return "{$user} deleted task {$task} from {$company}";
    
            case 'task_status_updated':
                return "{$user} changed the status of task {$task}";
    
            // --- Google Leads Events ---
            case 'leads_fetched_google':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $searchQuery = $data['s'] ?? 'unknown query';
                $count = $data['count'] ?? 0;
                $searchHighlight = "<span class='act-highlight act-highlight--google'>{$count} leads</span>";
                return "{$user} fetched {$searchHighlight} from Google using search: <span class='act-highlight act-highlight--query'>\"{$searchQuery}\"</span>";
    
            // --- Temporary Lead Events ---
            case 'saved_temp_lead':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $tempCompanyName = $data['company_name'] ?? 'Temporary Lead';
                $tempCompany = "<span class='act-highlight act-highlight--temp'>{$tempCompanyName}</span>";
                return "{$user} saved temporary lead {$tempCompany}";
    
            case 'saved_temp_leads':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);

                $savedCount = is_array($data['count']) ? json_encode($data['count']) : ($data['count'] ?? 0);
                $duplicates = is_array($data['duplicates']) ? json_encode($data['duplicates']) : ($data['duplicates'] ?? 0);
                $skipped = is_array($data['skipped']) ? json_encode($data['skipped']) : ($data['skipped'] ?? 0);

                $countHighlight = "<span class='act-highlight act-highlight--count'>{$savedCount}</span>";
                return "{$user} saved {$countHighlight} temporary leads from bulk import (Duplicates: {$duplicates}, Skipped: {$skipped})";
    
            case 'saved_main_lead_delete_temporary':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $tempCompanyName = $data['company_name'] ?? 'Temporary Lead';
                $newLeadId = $data['new_lead_id'] ?? 'N/A';
                $tempCompany = "<span class='act-highlight act-highlight--temp'>{$tempCompanyName}</span>";
                $leadIdHighlight = "<span class='act-highlight act-highlight--id'>#{$newLeadId}</span>";
                return "{$user} converted temporary lead {$tempCompany} to main lead {$leadIdHighlight}";
    
            case 'saved_main_google_leads_count':
                $savedCount = is_numeric($this->data) ? $this->data : 0;
                $countHighlight = "<span class='act-highlight act-highlight--count'>{$savedCount}</span>";
                return "{$user} saved {$countHighlight} leads from Google API fetch";
    
            case 'temp_note_added':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                return "{$user} added a new note to a temporary lead";
    
            case 'temp_lead_deleted':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $tempCompanyName = $data['company_name'] ?? 'Temporary Lead';
                $tempCompany = "<span class='act-highlight act-highlight--temp'>{$tempCompanyName}</span>";
                return "{$user} deleted temporary lead {$tempCompany}";
    
            case 'temp_leads_deleted':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $count = $data['count'] ?? 0;
                $countHighlight = "<span class='act-highlight act-highlight--count'>{$count}</span>";
                return "{$user} deleted {$countHighlight} temporary leads in bulk";
    
            case 'moved_temporary_leads_to_leads':
                $movedCount = is_numeric($this->data) ? $this->data : 0;
                $countHighlight = "<span class='act-highlight act-highlight--count'>{$movedCount}</span>";
                return "{$user} moved {$countHighlight} temporary leads to main leads";

            case 'loa_mail_sent':
                $movedCount = is_numeric($this->data) ? $this->data : 0;
                $countHighlight = "<span class='act-highlight act-highlight--count'>{$movedCount}</span>";
                return "LOA document email sent to customer for this lead {$countHighlight} time(s)";
                
            case 'admin_loa_verified':
                return "{$companyName} - LOA document Verified By Admin";

            case 'lead_approved_rfq':
                return "<b>{$companyName}</b> - Lead Approved & Proceed To RFQ";

            case 'lead_lost':
                return "<b>{$companyName}</b> - Lead Lost";

            case 'added_quote_to_lead':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $value = data_get($data, 'supplier_name');
                return "{$user} added a new quote From #{$value} to Lead: <b>{$companyName}</b>.";
                break;
                
            case 'updated_quote_on_lead':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $value = data_get($data, 'supplier_name');
                return "{$user} Updated quote From #{$value} on Lead: <b>{$companyName}</b>.";
                
            case 'deleted_quote_from_lead':
                $data = is_array($this->data) ? $this->data : json_decode($this->data, true);
                $value = data_get($data, 'supplier_name');
                return "{$user} Deleted quote from #{$value} From Lead: <b>{$companyName}</b>.";
                
            case 'assigned_supplier_to_lead':
                return "{$user} {$this->data}";
                
            default:
                return "Activity on {$company} logged by {$user}";
        }
    }
}