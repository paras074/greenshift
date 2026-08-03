<?php

use App\Services\CommonService;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\PriorityStatus;
use App\Models\Timeline;
use App\Models\LeadAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Events\NotificationCountUpdated;
use Illuminate\Support\Facades\Crypt;

function check(){
    return "heck";
}

function format_date($date){
    return date('d M, Y', strtotime($date));
}


function format_date_time($date_time){
    return date('d M, Y h:i:s', strtotime($date_time));
}


function get_all_lead_source(){
    $data = LeadSource::where('status', 'active')->orderBy('sort_order')->get();
    if(count($data) > 0){
        $data = $data->toArray();
    }else{
        $data = [];
    }
    return $data;
}


function get_all_lead_status(){
    $data = LeadStatus::where('status', 'active')->orderBy('sort_order')->get();
    if(count($data) > 0){
        $data = $data->toArray();
    }else{
        $data = [];
    }
    return $data;
}


function get_all_priority_status(){
    $data = PriorityStatus::where('status', 'active')->orderBy('sort_order')->get();
    if(count($data) > 0){
        $data = $data->toArray();
    }else{
        $data = [];
    }
    return $data;
}

if (!function_exists('log_timeline')) {
    function log_timeline($leadId = null, $data = null, $userId = null, $other = null, $taskId = null)
    {
        $resolvedUserId = ($userId ?: auth()->id()) ?: null;
        return Timeline::create([
            'lead_id' => $leadId,
            'user_id' => $resolvedUserId,
            'task_id' => $taskId,
            'data'    => is_array($data) ? json_encode($data) : $data,
            'other'   => is_array($other) ? json_encode($other) : $other,
        ]);
    }
}

if (!function_exists('GetAllUsersByRoleId')) {
    function GetAllUsersByRoleId(int $roleId) {
        return User::role($roleId)->where('status', 'active')->get();
    }
}

if (!function_exists('getAllAssignedUsers')) {
    function getAllAssignedUsers()
    {
        return User::whereHas('leadAssignments.lead')
            ->select('id', 'name')
            ->distinct()
            ->get();
    }
}

if (!function_exists('getAllLeadUsers')) {
    function getAllLeadUsers()
    {
        return User::whereIn('id', function($query) {
            $query->select('created_by')
                  ->from('leads')
                  ->whereNotNull('created_by');
        })
        ->when(auth()->check(), function ($query) {
            $query->where('id', '!=', auth()->id());
        })
        ->get();
    }
}

if (!function_exists('module_permissions')) {
    function module_permissions($module)
    {
        return collect(config("permissions.modules.$module.permissions", []))
            ->map(fn($perm) => "$perm $module")
            ->toArray();
    }
}
if (!function_exists('is_superadmin')) {
    function is_superadmin(): int
    {
        return (auth()->check() && auth()->user()->hasRole('superadmin')) ? 1 : 0;
    }
}

if (!function_exists('create_notification')) {
    function create_notification(
        $lead_id,
        $type = null,
        $kind = null,
        $to = 0,
        $message = null,
        $url = null,
        $data = null
    ) {
        return Notification::create([
            'lead_id' => $lead_id,
            'type' => $type,
            'kind' => $kind,
            'from' => auth()->check() ? auth()->id() : 0,
            'to' => $to,
            'is_read' => 0,
            'message' => $message,
            'url' => $url,
            'data' => $data,
        ]);
    }
}
if (!function_exists('notification_icon')) {
    function notification_icon($kind)
    {
        switch ($kind) {
            case 'call':
                return '<i class="bi bi-telephone-fill"></i>';

            case 'email':
                return '<i class="bi bi-envelope-fill"></i>';

            case 'follow_up':
                return '<i class="bi bi-calendar-check-fill"></i>';

            default:
                return '<i class="bi bi-bell-fill"></i>';
        }
    }
}
function unread_notifications_count()
{
    $userId = auth()->id();

    return Notification::where(function ($q) use ($userId) {
        if (is_superadmin()) {
            $q->where('to', $userId)->orWhere('to', 0);
        } else {
            $q->where('to', $userId);
        }
    })->where('is_read', 0)->count();
}

if (!function_exists('send_notification_email')) {

    function send_notification_email($to, $subject, array $data = [])
    {
        try {

            $html = view('layouts.email-templates.notification', [
                'data' => $data
            ])->render();

            Mail::html($html, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return true;

        } catch (\Exception $e) {
            \Log::error('Email Error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getDashboardTimeline')) {
    function getDashboardTimeline($limit = null, $lead_id = null)
    {
        if (!Auth::check() || !Auth::user()->can('view-timeline dashboard')) {
            return [];
        }

        // Eager load both lead and task to ensure $entry->task->title works
        $entries = Timeline::with(['user', 'lead', 'task'])
            ->when($lead_id, function ($query, $lead_id) {
                $query->where('lead_id', $lead_id);
            })
            ->latest()
            ->when($limit, function ($query, $limit) {
                return $query->limit($limit);
            })
            ->get();

        $formattedData = [];

        foreach ($entries as $entry) {
           
            $date = $entry->created_at->format('d M, Y');
            $headTitle = ucwords(str_replace('_', ' ', $entry->other));

            $companyName = $entry->lead->company_name ?? 'a Lead';
            $userName = $entry->user->name ?? 'System';
            
            // Task Title logic with a fallback
            $taskTitle = $entry->task->title ?? 'Untitled Task';

            switch ($entry->other) {
                // Original cases
                case 'lead_created':
                    $messageText = "{$userName} successfully created a new lead for <b>{$companyName}</b>.";
                    break;

                case 'lead_updated':
                    $messageText = "{$userName} modified the profile details of <b>{$companyName}</b>.";
                    break;

                case 'lead_deleted':
                    $messageText = "{$userName} permanently removed Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_assigned':
                    $messageText = "{$userName} assigned a team member to Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_assigned_bulk':
                    $messageText = "{$userName} assigned team members to Lead: <b>{$companyName}</b>.";
                    break;

                case 'admin_loa_generated':
                    $messageText = "{$userName} generated a new LOA for Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_unassigned':
                    $messageText = "{$userName} removed a team member from Lead: <b>{$companyName}</b>.";
                    break;

                case 'attachment_added':
                    $messageText = "{$userName} uploaded a new attachment to <b>{$companyName}</b>.";
                    break;

                case 'customer_added_attachments':
                    $messageText = "customer uploaded new attachment(s) to <b>{$companyName}</b>.";
                    break;
                    break;

                case 'customer_verified_signed_loa':
                    $messageText = "customer verified and signed the LOA for <b>{$companyName}</b>.";
                    break;

                case 'attachment_deleted':
                    $messageText = "{$userName} deleted an attachment from <b>{$companyName}</b>.";
                    break;

                case 'lead_status_updated':
                    $messageText = "{$userName} updated the status for <b>{$companyName}</b>.";
                    break;

                case 'task_created':
                    $messageText = "{$userName} created a new task: \"<b>{$taskTitle}</b>\" for {$companyName}.";
                    break;

                case 'task_updated':
                    $messageText = "{$userName} updated the details for task: \"<b>{$taskTitle}</b>\".";
                    break;

                case 'task_deleted':
                    $messageText = "{$userName} removed task: \"<b>{$taskTitle}</b>\" from {$companyName}.";
                    break;

                case 'task_status_updated':
                    $messageText = "{$userName} changed the status of task: \"<b>{$taskTitle}</b>\".";
                    break;

                // New Google Leads cases
                case 'leads_fetched_google':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $searchQuery = $data['s'] ?? 'unknown query';
                    $count = $data['count'] ?? 0;
                    $messageText = "{$userName} fetched <b>{$count} leads</b> from Google using search: \"<b>{$searchQuery}</b>\".";
                    break;

                case 'saved_temp_lead':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'a Temporary Lead';
                    $messageText = "{$userName} saved a temporary lead: <b>{$tempCompanyName}</b>.";
                    break;

                case 'saved_temp_leads':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $savedCount = is_array($data['count']) ? json_encode($data['count']) : ($data['count'] ?? 0);
                    $duplicates = is_array($data['duplicates']) ? json_encode($data['duplicates']) : ($data['duplicates'] ?? 0);
                    $skipped = is_array($data['skipped']) ? json_encode($data['skipped']) : ($data['skipped'] ?? 0);
                    $messageText = "{$userName} saved <b>{$savedCount}</b> temporary leads from bulk import (Duplicates: {$duplicates}, Skipped: {$skipped}).";
                    break;

                case 'saved_main_lead_delete_temporary':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'Temporary Lead';
                    $newLeadId = $data['new_lead_id'] ?? 'N/A';
                    $messageText = "{$userName} converted temporary lead <b>{$tempCompanyName}</b> to a main lead (ID: {$newLeadId}).";
                    break;

                case 'saved_main_google_leads_count':
                    $savedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "{$userName} successfully saved <b>{$savedCount}</b> leads from Google API fetch.";
                    break;

                case 'temp_note_added':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $messageText = "{$userName} added a new note to a temporary lead.";
                    break;
                    
                case 'signed_loa_customer':
                    $messageText = "Customer signed the LOA for <b>{$companyName}</b>.";
                    break;

                case 'temp_lead_deleted':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'a Temporary Lead';
                    $messageText = "{$userName} deleted temporary lead: <b>{$tempCompanyName}</b>.";
                    break;

                case 'temp_leads_deleted':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $count = $data['count'] ?? 0;
                    $messageText = "{$userName} deleted <b>{$count}</b> temporary leads in bulk.";
                    break;

                case 'moved_temporary_leads_to_leads':
                    $movedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "{$userName} moved <b>{$movedCount}</b> temporary leads to main leads.";
                    break;

                case 'loa_mail_sent':
                    $movedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "LOA document email sent to customer for this lead {$movedCount} times";
                    break;

                case 'admin_loa_verified':
                    $messageText = "<b>{$companyName}</b> - LOA document Verified By Admin";
                    break;

                case 'lead_approved_rfq':
                    $messageText = "<b>{$companyName}</b> - Lead Approved & Proceed To RFQ";
                    break;

                case 'lead_lost':
                    $messageText = "<b>{$companyName}</b> - Lead Lost";
                    break;
                    
                case 'added_quote_to_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} added a new quote #{$value} to Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'updated_quote_on_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} Updated quote #{$value} on Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'deleted_quote_from_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} Deleted quote #{$value} From Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'assigned_supplier_to_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} {$entry->data}";
                    break;

                default:
                    $messageText = "Activity logged for <b>{$companyName}</b> by {$userName}.";
                    break;
            }

            $formattedData[] = [
                'date'    => $date,
                'head'    => $headTitle,
                'text'    => $messageText,
                'lead_id' => $entry->lead_id,
                'task_id' => $entry->task_id,
                'type'    => $entry->other // This allows you to use your $map[$entry->other] for colors/icons in Blade
            ];
        }

        return $formattedData;
    }
}

if (!function_exists('getLeadTimeline')) {
    function getLeadTimeline($leadId)
    {
        if (!Auth::check() || !Auth::user()->can('view-timeline dashboard')) {
            return [];
        }

        $entries = Timeline::with(['user', 'lead'])
            ->where('lead_id', $leadId)
            ->latest()
            ->get();

        $formattedData = [];
          
        foreach ($entries as $entry) {
            $date = $entry->created_at->diffForHumans();

            $headTitle = ucwords(str_replace('_', ' ', $entry->other));

            $companyName = $entry->lead->company_name ?? 'a Lead';
            $userName = $entry->user->name ?? 'System';
            
            // Task Title logic with a fallback
            $taskTitle = $entry->task->title ?? 'Untitled Task';

            switch ($entry->other) {
                // Original cases
                case 'lead_created':
                    $messageText = "{$userName} successfully created a new lead for <b>{$companyName}</b>.";
                    break;

                case 'lead_updated':
                    $messageText = "{$userName} modified the profile details of <b>{$companyName}</b>.";
                    break;

                case 'lead_deleted':
                    $messageText = "{$userName} permanently removed Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_assigned':
                    $messageText = "{$userName} assigned a team member to Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_assigned_bulk':
                    $messageText = "{$userName} assigned team members to Lead: <b>{$companyName}</b>.";
                    break;

                case 'admin_loa_generated':
                    $messageText = "{$userName} generated a new LOA for Lead: <b>{$companyName}</b>.";
                    break;

                case 'member_unassigned':
                    $messageText = "{$userName} removed a team member from Lead: <b>{$companyName}</b>.";
                    break;

                case 'attachment_added':
                    $messageText = "{$userName} uploaded a new attachment to <b>{$companyName}</b>.";
                    break;

                case 'customer_added_attachments':
                    $messageText = "customer uploaded new attachment(s) to <b>{$companyName}</b>.";
                    break;
                    break;

                case 'customer_verified_signed_loa':
                    $messageText = "customer verified and signed the LOA for <b>{$companyName}</b>.";
                    break;

                case 'attachment_deleted':
                    $messageText = "{$userName} deleted an attachment from <b>{$companyName}</b>.";
                    break;

                case 'lead_status_updated':
                    $messageText = "{$userName} updated the status for <b>{$companyName}</b>.";
                    break;

                case 'signed_loa_customer':
                    $messageText = "Customer signed the LOA for <b>{$companyName}</b>.";
                    break;

                case 'task_created':
                    $messageText = "{$userName} created a new task: \"<b>{$taskTitle}</b>\" for {$companyName}.";
                    break;

                case 'task_updated':
                    $messageText = "{$userName} updated the details for task: \"<b>{$taskTitle}</b>\".";
                    break;

                case 'task_deleted':
                    $messageText = "{$userName} removed task: \"<b>{$taskTitle}</b>\" from {$companyName}.";
                    break;

                case 'task_status_updated':
                    $messageText = "{$userName} changed the status of task: \"<b>{$taskTitle}</b>\".";
                    break;

                // New Google Leads cases
                case 'leads_fetched_google':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $searchQuery = $data['s'] ?? 'unknown query';
                    $count = $data['count'] ?? 0;
                    $messageText = "{$userName} fetched <b>{$count} leads</b> from Google using search: \"<b>{$searchQuery}</b>\".";
                    break;

                case 'saved_temp_lead':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'a Temporary Lead';
                    $messageText = "{$userName} saved a temporary lead: <b>{$tempCompanyName}</b>.";
                    break;

                case 'saved_temp_leads':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $savedCount = is_array($data['count']) ? json_encode($data['count']) : ($data['count'] ?? 0);
                    $duplicates = is_array($data['duplicates']) ? json_encode($data['duplicates']) : ($data['duplicates'] ?? 0);
                    $skipped = is_array($data['skipped']) ? json_encode($data['skipped']) : ($data['skipped'] ?? 0);
                    $messageText = "{$userName} saved <b>{$savedCount}</b> temporary leads from bulk import (Duplicates: {$duplicates}, Skipped: {$skipped}).";
                    break;

                case 'saved_main_lead_delete_temporary':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'Temporary Lead';
                    $newLeadId = $data['new_lead_id'] ?? 'N/A';
                    $messageText = "{$userName} converted temporary lead <b>{$tempCompanyName}</b> to a main lead (ID: {$newLeadId}).";
                    break;

                case 'saved_main_google_leads_count':
                    $savedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "{$userName} successfully saved <b>{$savedCount}</b> leads from Google API fetch.";
                    break;

                case 'temp_note_added':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $messageText = "{$userName} added a new note to a temporary lead.";
                    break;

                case 'temp_lead_deleted':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $tempCompanyName = $data['company_name'] ?? 'a Temporary Lead';
                    $messageText = "{$userName} deleted temporary lead: <b>{$tempCompanyName}</b>.";
                    break;

                case 'temp_leads_deleted':
                    $data = is_array($entry->data) ? $entry->data : json_decode($entry->data, true);
                    $count = $data['count'] ?? 0;
                    $messageText = "{$userName} deleted <b>{$count}</b> temporary leads in bulk.";
                    break;

                case 'moved_temporary_leads_to_leads':
                    $movedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "{$userName} moved <b>{$movedCount}</b> temporary leads to main leads.";
                    break;

                case 'loa_mail_sent':
                    $movedCount = is_numeric($entry->data) ? $entry->data : 0;
                    $messageText = "LOA document email sent to customer for this lead {$movedCount} times";
                    break;
                    
                case 'admin_loa_verified':
                    $messageText = "{$companyName} - LOA document Verified By Admin";
                    break;

                case 'lead_approved_rfq':
                    $messageText = "<b>{$companyName}</b> - Lead Approved & Proceed To RFQ";
                    break;

                case 'lead_lost':
                    $messageText = "<b>{$companyName}</b> - Lead Lost";
                    break;
                    
                case 'added_quote_to_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} added a new quote #{$value} to Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'updated_quote_on_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} Updated quote #{$value} on Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'deleted_quote_from_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} Deleted quote #{$value} From Lead: <b>{$companyName}</b>.";
                    break;
                    
                case 'assigned_supplier_to_lead':
                    $data = json_decode($entry->data, true);
                    $value = data_get($data, 'supplier_name');
                    $messageText = "{$userName} {$entry->data}";
                    break;

                default:
                    $messageText = "Activity logged for <b>{$companyName}</b> by {$userName}.";
                    break;
            }

            $formattedData[] = [
                'date'    => $date,
                'head'    => $headTitle,
                'text'    => $messageText,
                'lead_id' => $entry->lead_id,
                'task_id' => $entry->task_id,
                'type'    => $entry->other // This allows you to use your $map[$entry->other] for colors/icons in Blade
            ];
        }           


        return $formattedData;
    }
}

if (!function_exists('tasksStatus')) {
    function tasksStatus()
    {
        return [
            'pending'      => 'Pending',
            'in-progress'  => 'In Progress',
            'completed'    => 'Completed',
            'overdue'      => 'Overdue',
            'upcoming'     => 'Upcoming',
        ];
    }
}

if (!function_exists('taskPriorities')) {
    function taskPriorities()
    {
        return [
            'low'    => 'Low',
            'medium' => 'Medium',
            'high'   => 'High',
        ];
    }
}

if (!function_exists('taskTypes')) {
    function taskTypes()
    {
        return [
            'call'           => 'Call',
            'email'          => 'Email',
            'site-visit'     => 'Site Visit',
            'send-proposal'  => 'Send Proposal',
            'follow-up'      => 'Follow Up',
            'meeting'        => 'Meeting',
        ];
    }
}

if (!function_exists('KanbanLeadstatus')) {
    function KanbanLeadstatus() {
        static $kanbanSteps = [];
        
        if (empty($kanbanSteps)) {
            $kanbanSteps = \App\Models\LeadStatus::where('status', 'active')
                ->where('show_kanban', 1)
                ->orderBy('sort_order', 'asc')
                ->get(['id', 'name', 'color'])
                ->keyBy('id')
                ->map(function ($item) {
                    return [
                        'name'  => $item->name,
                        'color' => $item->color,
                    ];
                })
                ->toArray();
        }               
        
        return $kanbanSteps;
    }
}

if (!function_exists('formatBudgetRange')) {
    function formatBudgetRange($range) {
        if (empty($range)) return 'N/A';

        return preg_replace_callback('/\d+/', function($matches) {
            $number = $matches[0];
            return number_format($number);
        }, $range);
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol() {
        return '£'; 
    }
}

if (!function_exists('get_user_tasks_due_today')) {
    function get_user_tasks_due_today()
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        return Task::where('end_date', $today)
            ->where('status', '!=', 'completed') // Optional: hide finished tasks
            ->where(function ($query) use ($userId) {
                $query->whereJsonContains('assign_to', (string)$userId)
                      ->orWhere(function ($subQuery) use ($userId) {
                          $subQuery->where(function($q) {
                              $q->whereNull('assign_to')
                                ->orWhere('assign_to', '[]')
                                ->orWhere('assign_to', '');
                          })
                          ->where('assign_by', $userId);
                      });
            })
            ->get();
    }
}


if (!function_exists('get_setting_data')) {
    function get_setting_data($keys = null) {
        $settings = DB::table('core_settings')->pluck('setting_value', 'setting_key')->map(fn($value) => trim($value, '"'));
        if (is_null($keys)) {
            return $settings;
        }
        if (is_array($keys)) {
            return collect($keys)->mapWithKeys(function ($key) use ($settings) {
                return [$key => $settings[$key] ?? null];
            })->toArray();
        }
        return $settings[$keys] ?? null;
    }
}

if (!function_exists('is_superadmin_by_id')) {
    function is_superadmin_by_id($userId): int {
        $user = User::find($userId);
        return ($user && $user->hasRole('superadmin')) ? 1 : 0;
    }
}

if (!function_exists('triggerNotificationCount')) {

    function triggerNotificationCount($userId)
    {
        $query = Notification::query();
        if (is_superadmin_by_id($userId)) {
            $query->where(function ($q) use ($userId) {
                $q->where('to', $userId)
                  ->orWhere('to', 0);
            });
        } else {
            $query->where('to', $userId);
        }
        $unreadCount = (clone $query)
            ->where('is_read', 0)
            ->count();

        broadcast(new NotificationCountUpdated([
            'user_id' => $userId,
            'unread_count' => $unreadCount,
        ]));

        return $unreadCount;
    }
}

if (!function_exists('decrypt_setting_value')) {

    function decrypt_setting_value($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}