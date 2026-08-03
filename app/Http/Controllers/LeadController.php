<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Models\LeadStatus;
use App\Models\Note;
use App\Models\Lead;
use App\Models\LeadAttachment;
use App\Models\User;
use App\Models\LeadAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\CallLog;
use App\Models\RfqQuote;

class LeadController extends Controller{
    // =========================================================
    //  LIST
    // =========================================================
    public function index(Request $request){
        $this->authorize('view leads');
        $user = auth()->user();
        $query = Lead::with(['leadStatus', 'priorityStatus'])->withCount('assignments')->latest();
        
        // if request is ajax then (mainly works for filter)
        if ($request->ajax()) {
            $columns = Schema::getColumnListing('leads');

            $canViewOwn = $user->can('view-own leads');

            if (!is_superadmin()) {
                if ($canViewOwn) {
                    $query->whereHas('assignments', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                } else {
                    $query->whereRaw('1=0');
                }
            }
           
            foreach ($request->all() as $key => $value) {
                if (!request()->filled($key) || !in_array($key, $columns)) {
                    if($key !== 'assigned_to'){
                        continue;
                    }
                }

                if ($key === 'created_at') {
                    // here is this week filter
                    if($value == 1){
                        $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    } elseif($value == 2){  // this filter works for current month
                        $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                    } elseif($value == 3 && $request->filled('created_at_range')){ // this filter works for custom range selection
                        $range = $request->created_at_range;
                        if (str_contains($range, ' to ')) {
                            $dates = explode(' to ', $range);
                            $startDate = Carbon::parse($dates[0])->startOfDay();
                            $endDate = Carbon::parse($dates[1])->endOfDay();
                            $query->whereBetween('created_at', [$startDate, $endDate]);
                        } else {
                            // Single date selected
                            $query->whereDate('created_at', Carbon::parse($range));
                        }
                    }
                } elseif($key === 'assigned_to'){
                    if ($request->filled('assigned_to')) {
                        $query->whereHas('assignments', function($q) use ($value) {
                            $q->where('user_id', $value);
                        });
                    }
                } else {
                    $query->where($key, $value);
                }
            }
            $leads = $query->get();
            return view('leads.partials.row', compact('leads'))->render();
        }
        if(!is_superadmin()){
            if ($user->can('view-own leads')) {
                $query->whereHas('assignments', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }
        $leads = $query->get();

        $statusCounts = Lead::selectRaw('lead_status_id, COUNT(*) as total')

            ->when(!is_superadmin() && $user->can('view-own leads'), function ($q) use ($user) {
                $q->whereHas('assignments', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                });
            })

            ->groupBy('lead_status_id')
            ->pluck('total', 'lead_status_id');
        
        
        return view('leads.index', compact('leads', 'statusCounts'));
    }


    // =========================================================
    //  SHOW (view single lead)
    // =========================================================
    public function show(Lead $lead){
        $this->authorize('view leads'); 
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }
        $assignedUsers = $lead->assignments()->with('user')->get()->pluck('user');
        $tasks = $lead->tasks()->with(['assignedBy', 'latestNote'])->orderBy('created_at', 'desc')->get();
        $scheduledTask = $tasks->where('is_scheduled', 1)->first();
        $hasScheduledTask = !empty($scheduledTask);

        // Fetch call logs utilizing the new Eloquent Model with its user relationship loaded
        $callLogs = CallLog::with('user')
                ->where('lead_id', $lead->id)
                ->latest()->get();

        $leadMetrics = [
            'total_calls' => $callLogs->count(),

            'total_duration' => (function($ms) {
                if ($ms <= 0) return '0s';
                $totalSeconds = floor($ms / 1000);
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds / 60) % 60);
                $seconds = $totalSeconds % 60;
                
                $parts = [];
                if ($hours > 0) $parts[] = "{$hours}h";
                if ($minutes > 0) $parts[] = "{$minutes}m";
                if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds}s";
                return implode(' ', $parts);
            })($callLogs->sum('duration')),

            'recordings_available' => $callLogs->filter(function ($item) {
                return !is_null($item->local_recording_path) && $item->local_recording_path !== '';
            })->count(),

            'recordings_missing' => $callLogs->filter(function ($item) {
                return is_null($item->local_recording_path) || $item->local_recording_path === '';
            })->count()
        ];
        
        $quotes = $lead->rfqQuotes()->latest()->get();

        return view('leads.show', compact('lead', 'assignedUsers', 'tasks', 'scheduledTask', 'hasScheduledTask', 'callLogs', 'leadMetrics', 'quotes'));
    }


    // =========================================================
    //  SHOW CREATE FORM
    // =========================================================
    public function create(){
        $this->authorize('create leads');
        return view('leads.manage-lead');
    }

    // =========================================================
    //  SHOW EDIT FORM
    // =========================================================
    public function edit(Lead $lead){
        $this->authorize('edit leads');
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }
        $assignedUserIds = $lead->assignments()->pluck('user_id')->toArray();
        $lead->load(['attachments.uploader']);

        $communicationNotes = Note::with(['user:id,name', 'mentionedUser:id,name'])
                ->where('lead_id', $lead->id)
                ->whereIn('others', ['call', 'email', 'whatsapp'])
                ->latest()
                ->get();

        // Fetch call logs utilizing the new Eloquent Model with its user relationship loaded
        $callLogs = CallLog::with('user')
                ->where('lead_id', $lead->id)
                ->latest()->get();

        $leadMetrics = [
            'total_calls' => $callLogs->count(),

            'total_duration' => (function($ms) {
                if ($ms <= 0) return '0s';
                $totalSeconds = floor($ms / 1000);
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds / 60) % 60);
                $seconds = $totalSeconds % 60;
                
                $parts = [];
                if ($hours > 0) $parts[] = "{$hours}h";
                if ($minutes > 0) $parts[] = "{$minutes}m";
                if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds}s";
                return implode(' ', $parts);
            })($callLogs->sum('duration')),

            'recordings_available' => $callLogs->filter(function ($item) {
                return !is_null($item->local_recording_path) && $item->local_recording_path !== '';
            })->count(),

            'recordings_missing' => $callLogs->filter(function ($item) {
                return is_null($item->local_recording_path) || $item->local_recording_path === '';
            })->count()
        ];                
        return view('leads.manage-lead', compact('lead', 'assignedUserIds', 'communicationNotes', 'callLogs', 'leadMetrics'));
    }

    public function edit_details(Lead $lead){
        $this->authorize('edit leads');
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }
        $assignedUserIds = $lead->assignments()->pluck('user_id')->toArray();
		return view('leads.edit-details', compact('lead', 'assignedUserIds'));
    }

    public function bulkDelete(Request $request)
    {
        $this->authorize('delete leads');

        $leadIds = $request->lead_ids;

        if (empty($leadIds) || !is_array($leadIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No leads selected.'
            ], 400);
        }

        $leads = Lead::whereIn('id', $leadIds)->get();

        foreach ($leads as $lead) {
            log_timeline($lead->id, $lead->toArray(), null, 'lead_deleted');
            $lead->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Selected leads deleted successfully.'
        ]);
    }


    // =========================================================
    //  UNIFIED: manage_lead  →  handles CREATE + UPDATE + DELETE
    // =========================================================
    public function manage_lead(Request $request, ?Lead $lead = null)
    {
        // ── DELETE ──────────────────────────────────────────
        if ($request->input('_action') === 'delete') {
            $this->authorize('delete leads');
    
            log_timeline($lead->id, $lead->toArray(), null, 'lead_deleted');
    
            $lead->delete();
    
            return redirect()
                ->route('leads.index')
                ->with('success', 'Lead deleted successfully.');
        }
    
        // ── VALIDATION (shared for create & update) ─────────
        $rules = [
            'company_name'              => 'required|string',
            'reg_number'                => 'nullable|string',
            'email'                     => 'nullable|email',
            'phone'                     => 'nullable|string',
            'annual_consumption'        => 'nullable|string',
            'total_annual_consumption'  => 'sometimes|nullable|string',
            'budget_range'              => 'sometimes|nullable|string',
            'roof_site_type'            => 'sometimes|nullable|string',
            'decision_maker_name'       => 'nullable|string',
            'decision_maker_designation'=> 'nullable|string',
            'mpan'                      => 'nullable|string',
            'aq'                        => 'nullable|string',
            'address'                   => 'nullable|string',
            'city'                      => 'nullable|string',
            'state'                     => 'nullable|string',
            'postcode'                  => 'nullable|string',
            'description'               => 'nullable|string',
            'current_supplier'          => 'nullable|string',
            'energy_type'               => 'nullable',
            'lead_status_id'            => 'required|integer',
            'contract_end_date'         => 'nullable|date',
            'priority_status_id'        => 'required|integer',
            'status'                    => 'nullable|in:active,draft',
        ];
    
        $validated = $request->validate($rules);
    
        // Store custom fields in "others" JSON column
        $others = [
            'decision_maker_designation' => $request->decision_maker_designation,
            'address'                    => [] 
        ];
        
        $i = 1;
        while (
            $request->has("address_$i") || 
            $request->has("city_$i") || 
            $request->has("state_$i") || 
            $request->has("postcode_$i")
        ) {
            $addr     = $request->input("address_$i");
            $city     = $request->input("city_$i");
            $state    = $request->input("state_$i");
            $postcode = $request->input("postcode_$i");

            if (!empty($addr) || !empty($city) || !empty($state) || !empty($postcode)) {
                $others['address'][] = [
                    'address'  => $addr ?? '',
                    'city'     => $city ?? '',
                    'state'    => $state ?? '',
                    'postcode' => $postcode ?? '',
                ];
            }
            $i++;
        }
        
        // Remove from validated array since it's not a DB column
        unset($validated['decision_maker_designation']);
    
        // ── UPDATE ───────────────────────────────────────────
        if ($lead && $lead->exists) {
            $this->authorize('edit leads');
    
            $lead->update([
                ...$validated,
                'others' => array_merge($lead->others ?? [], $others),
            ]);
    
            $this->saveLeadAssignments($lead, $request);
    
            log_timeline($lead->id, $validated, null, 'lead_updated');
    
            return redirect()
                ->route('leads.edit', $lead)
                ->with('success', 'Lead updated successfully.');
        }
    
        // ── CREATE ───────────────────────────────────────────
        $this->authorize('create leads');
    
        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['others'] = $others;
    
        $lead = Lead::create($validated);
    
        $this->saveLeadAssignments($lead, $request);
    
        log_timeline($lead->id, $validated, null, 'lead_created');
    
        $this->CreateNotification($lead);
    
        triggerNotificationCount(1);
    
        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    private function saveLeadAssignments($lead, $request)
    {
        $user = auth()->user();
        if ($user->can('assign leads')) {
            // Merge both arrays from the request into one list of User IDs
            $managers = $request->input('assigned_manager', []);
            $executives = $request->input('assigned_executive', []);
            $allNewUserIds = array_unique(array_merge($managers, $executives));

            // 1. Get current IDs from DB
            $existingIds = LeadAssignment::where('lead_id', $lead->id)
                ->pluck('user_id')
                ->toArray();

            // 2. Find IDs to DELETE
            $toDelete = array_diff($existingIds, $allNewUserIds);
            if (!empty($toDelete)) {
                LeadAssignment::where('lead_id', $lead->id)
                    ->whereIn('user_id', $toDelete)
                    ->delete();
            }

            // 3. Find IDs to INSERT
            $toInsert = array_diff($allNewUserIds, $existingIds);
            if (!empty($toInsert)) {
                foreach ($toInsert as $userId) {
                    LeadAssignment::create([
                        'lead_id' => $lead->id,
                        'user_id' => $userId
                    ]);
                }
                log_timeline($lead->id, $toInsert, null, 'member_assigned');
            }
        }
    }

    public function getAssignedMembers(Lead $lead)
    {
        try {
            // Eager load the user AND their roles
            $assignments = $lead->assignments()
                ->with(['user' => function($query) {
                    // Select basic info and include the roles relationship
                    $query->select('id', 'name', 'email')->with('roles:id,name');
                }])
                ->get();

            // Transform the data to make the role name easier to access if needed
            $data = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'user_name' => $assignment->user->name,
                    'user_email' => $assignment->user->email,
                    // This returns an array of role names, e.g., ["Admin", "Manager"]
                    'roles' => $assignment->user->getRoleNames(), 
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function CreateNotification($lead){
        $userName = auth()->check() ? auth()->user()->name : 'System';
        create_notification(
            $lead->id,
            'to_admin',
            'email',
            0,
            "Created new lead by {$userName}, have a look.",
            parse_url(route('leads.show', $lead), PHP_URL_PATH)
        );

        $data = array(
            'name' => $userName,
            'message' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur a feugiat mauris. Pellentesque urna erat, pulvinar eu volutpat sit amet, iaculis et eros. Aliquam vestibulum viverra finibus. "
        );
        $emails = User::role('superadmin')->pluck('email')->toArray();
        foreach ($emails as $email) {
            //send_notification_email($email, 'New Lead Created - '.$lead->company_name, $data);
        }
    }

    public function attachFile(Request $request, Lead $lead)
    {
        // 1. Validate the individual file
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string',
            'others' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');

            // 2. Physical Upload
            $path = $file->store('leads/attachments', 'public');

            // 3. Save to Database
            $attachment = $lead->attachments()->create([
                'uploaded_by' => auth()->id(),
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => $request->description,
                'others'      => $request->others,
            ]);

            log_timeline($lead->id, asset('storage/' . $attachment->file_path), null, 'attachment_added');

            // 4. Return JSON for the frontend to update the UI
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->file_name,
                    'size' => $attachment->readable_size, // Uses the accessor we made earlier
                    'url' => asset('storage/' . $attachment->file_path)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAttachment(LeadAttachment $attachment)
    {
        try {
            // delete file from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            log_timeline($attachment->lead_id, asset('storage/' . $attachment->file_path), null, 'attachment_deleted');

            // delete from DB
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'receiver_email' => 'required|email',
            'verification_url' => 'required',
            'edited_lead_id' => 'required|integer|exists:leads,id'
        ]);

        try {

            $email = $request->receiver_email;
            $url = $request->verification_url;
            $leadId = $request->edited_lead_id;

            $lead = Lead::findOrFail($leadId);

            if($lead){
                $others = $lead->others ?? [];
                if(!isset($others['loa_generated'])){
                    return response()->json([
                        'success' => false,
                        'message' => 'LOA document is missing. Please generate the LOA before sending the verification email.',
                        'error_type' => 'LOA_MISSING'
                    ], 422);
                }
            }

            Mail::send([], [], function ($message) use ($email, $url, $lead) {

                $message->to($email)
                ->subject('Secure Verification Portal')
                ->html("
                    <h2>Secure Verification Portal</h2>

                    <p>Please click the link below to upload bills and e-sign the LOA.</p>

                    <p>
                        <a href='{$url}' target='_blank'>
                            Open Secure Portal
                        </a>
                    </p>

                    <p>
                        Please enter the following pass key when prompted:
                    </p>

                    <h3 style='letter-spacing:2px;'>
                        {$lead->pass_key}
                    </h3>

                    <p>Your information is encrypted and secure.</p>
                ");
            });

            // Others JSON handling
            $others = $lead->others ?? [];

            $others['loa_mails_sent'] = ($others['loa_mails_sent'] ?? 0) + 1;

            $lead->others = $others;
            if (empty($lead->email)) {
                $lead->email = $request->receiver_email;
            }

            $lead->save();

            log_timeline($lead->id, $others['loa_mails_sent'], null, 'loa_mail_sent');

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully.',
                'loa_mails_sent' => $others['loa_mails_sent']
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function loa_verify($data, Request $request)
    {
        $decoded = base64_decode(urldecode($data));
        $array = json_decode($decoded, true);

        $lead = Lead::findOrFail($array['lead_id'] ?? 0);

        $verified = true; // here need to set it false
        $message = null;

        $encodedKey = $request->query('k');
        $decodedQueryKey = $encodedKey
            ? base64_decode($encodedKey)
            : null;

        if ($decodedQueryKey && $lead->pass_key === $decodedQueryKey) {
            $verified = true;
        } else {
            if($decodedQueryKey){
                $message = "Verification failed. Invalid or missing pass key.";
            }            
        }
        

        $loa_verified = isset($lead->others['loa_signed']) && $lead->others['loa_signed'] === '1';

        $loa_sent = isset($lead->others['signable_status']) && $lead->others['signable_status'] === 'sent';

        $lead_loa_verified = isset($lead->others['lead_loa_verified']) && $lead->others['lead_loa_verified'] === 'yes';

        $loa_generated = $lead->others['loa_generated'] ?? '';
        if($loa_generated){
            $loa_generated = asset($loa_generated);
        }

        return view('verify.index', compact(
            'lead', 
            'verified', 
            'message', 
            'loa_verified', // Will be true if signed, false otherwise
            'lead_loa_verified', // Will be true if verify button clicked, false otherwise
            'loa_sent',       // Will be true if status is 'sent', false otherwise
            'loa_generated'  // Will contain the generated LOA information
        ));
    }

    public function verifyPasskey(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'pass_key' => 'required',
            'current_url' => 'required|url',
        ]);

        $lead = Lead::findOrFail($request->lead_id);

        if ($lead->pass_key !== $request->pass_key) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid passkey.'
            ]);
        }

        $encodedKey = base64_encode($request->pass_key);

        $redirectUrl = $request->current_url . '?k=' . urlencode($encodedKey);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl
        ]);
    }

    public function loa_upload_verify(Request $request)
    {
        // 1. Validate the array of files and the required lead_id
        $request->validate([
            'lead_id' => 'required|exists:leads,id', // Ensures the lead actually exists
            'files'   => 'sometimes|nullable|array',
            'files.*' => 'required_with:files|file|mimes:pdf,jpg,jpeg,png|max:10240', 
        ]);

        try {
            // Find the lead using the ID sent via AJAX
            $lead = Lead::findOrFail($request->input('lead_id'));
            $attached_files = [];
            // 2. Loop and process if files were actually attached
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    
                    $path = $file->store('leads/attachments', 'public');

                    $attachment = $lead->attachments()->create([
                        'uploaded_by' => null,
                        'file_path'   => $path,
                        'file_name'   => $file->getClientOriginalName(),
                        'file_type'   => $file->getClientMimeType(),
                        'file_size'   => $file->getSize(),
                        'description' => 'Customer attached file',
                        'others'      => null,
                    ]);

                    $attached_files[] = asset('storage/' . $attachment->file_path);
                }
            }

            if(!empty($attached_files)){
                log_timeline($lead->id, $attached_files, null, 'customer_added_attachments');
            }

            if($lead){
                $others = $lead->others;
                $others['lead_loa_verified'] = 'yes';
                $lead->others = $others;
                $lead->save();
                log_timeline($lead->id, 'Received signed LOA and attachments from customer', null, 'customer_verified_signed_loa');
                $this->CreateLOAVerifYNotification($lead);
            }
            
            return response()->json([
                'success' => true
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function CreateLOAVerifYNotification($lead){
        create_notification(
            $lead->id,
            'lead_owner',
            'email',
            $lead->created_by,
            "Customer verified and uploaded signed LOA for {$lead->company_name}.",
            parse_url(route('leads.show', $lead), PHP_URL_PATH)
        );
        triggerNotificationCount($lead->created_by);

    }

    public function verifyLoa(Lead $lead, Request $request)
    {
        $this->authorize('approve loa');
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }

        $others = $lead->others ?? [];
        if (empty($others['loa_signed'] == 1)) {
            return redirect()->back()->with('error', 'Signed LOA is missing.');
        }

        $signedLoaAttachments = $lead->attachments->filter(function ($attachment) {
            return (int) data_get($attachment, 'others.is_signed_loa', 0) === 1;
        })->values();

        return view('leads.verify-loa', compact('lead', 'signedLoaAttachments'));
    }
    
    public function VerifyLeadLoa(Request $request)
    {
        $this->authorize('approve loa');
        
        $request->validate([
            'lead_id'   => 'required|exists:leads,id',
            // 'loa_notes' => 'nullable|string',
        ]);
    
        $lead = Lead::findOrFail($request->lead_id);
        
        $others = $lead->others ?? [];
        
        $others['loa_verified'] = 1;
        $others['loa_verified_at'] = now()->toDateTimeString();
        $others['loa_verified_by'] = auth()->id();
        // $others['loa_admin_message'] = $request->input('loa_notes');
        
        $lead->others = $others;
        $lead->lead_status_id = 3;
        $lead->save();
        
        log_timeline($lead->id, 'Loa Verified by Greenshift Energy Limited', null, 'admin_loa_verified');
        
        return redirect()->route('leads.index')->with('success', 'LOA verified successfully for ' . $lead->company_name);
    }
    
    // RFQ
    public function go_to_rfq(Lead $lead, Request $request)
    {
        $this->authorize('approve loa');
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }

        $others = $lead->others ?? [];
        if (empty($others['loa_verified'] == 1)) {
            return redirect()->back()->with('error', 'LOA not Verified yet');
        }
        
        $assignedUsers = $lead->assignments()->with('user')->get()->pluck('user');
        
        return view('leads.proceed-rfq', compact('lead', 'assignedUsers'));
    }
    public function proceed_to_rfq(Request $request)
    {
        $this->authorize('approve loa');
        $request->validate([
            'lead_id'   => 'required|exists:leads,id',
            'rfq_admin_note' => 'nullable|string',
        ]);
        
        $leadId = $request->input('lead_id');
        $note = $request->input('rfq_admin_note');
        $action = $request->input('action');
        
        $lead = Lead::findOrFail($leadId);
        $others = $lead->others ?? [];

        if ($action === 'approve') {
            $others['proceed_to_rfq'] = 1;
            $others['proceed_to_rfq_at'] = now()->toDateTimeString();
            $others['proceed_to_rfq_by'] = auth()->id();
        
            $lead->others = $others;
            $lead->lead_status_id = 4;
            $lead->save();
            
            if (!empty(trim($note))) {
                $others['proceed_to_rfq_note'] = $note;

                Note::create([
                    'lead_id'      => $leadId,
                    'user_id'      => Auth::id(),
                    'data'         => 'Approved to RFQ : ' . $note,
                    'mentioned_id' => null,
                    'others'       => 'proceed_to_rfq',
                ]);
            }
            
            log_timeline($leadId, 'Moved TO RFQ by Greenshift Energy Limited', null, 'lead_approved_rfq');
            
            return redirect()->route('leads.index')->with('success', 'Lead approved and proceeded to RFQ successfully! ');

        } elseif ($action === 'lost') {
            
            $others['rejected_at'] = now()->toDateTimeString();
            $others['rejected_by'] = auth()->id();
        
            $lead->others = $others;
            $lead->lead_status_id = 6;
            $lead->save();
            
            if (!empty(trim($note))) {
                $others['reject_reason'] = $note;
                
                Note::create([
                    'lead_id'      => $leadId,
                    'user_id'      => Auth::id(),
                    'data'         => 'Lead Marked Lost: ' . $note,
                    'mentioned_id' => null,
                    'others'       => 'mark_lost',
                ]);
            }
            
            log_timeline($leadId, 'Lead lost', null, 'lead_lost');
            
            return redirect()->route('leads.index')->with('error', 'Lead has been marked as lost.');
        }
        return redirect()->back()->with('error', 'Something May be wrong.');
    }
    
    public function add_rfq(Lead $lead, Request $request){
        $this->authorize('create rfq');
        
        $user = auth()->user();
        if (!is_superadmin() && $user->can('view-own leads')) {
            $isAssigned = $lead->assignments()->where('user_id', $user->id)->exists();
            if (!$isAssigned) {
                abort(403, 'Unauthorized');
            }
        }

        $others = $lead->others ?? [];
        if (isset($others['loa_signed']) && empty($others['loa_signed'] == 1)) {
            return redirect()->back()->with('error', 'Signed LOA is missing.');
        }

        $signedLoaAttachments = $lead->attachments->filter(function ($attachment) {
            return (int) data_get($attachment, 'others.is_signed_loa', 0) === 1;
        })->values();

        return view('rfq.add-rfq', compact('lead', 'signedLoaAttachments'));
    }
    
    
    
}
