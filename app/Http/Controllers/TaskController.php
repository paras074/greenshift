<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Lead;
use App\Models\TaskNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    // Fetch all tasks
    public function index(Request $request)
    {
        $this->authorize('view tasks');
        $query = Task::with(['lead', 'assignedBy'])->latest();
        $user = auth()->user();

        if(!is_superadmin()){
            if ($user->can('view-own tasks')) {
                $query->whereRaw('JSON_CONTAINS(assign_to, JSON_QUOTE(CAST(? AS CHAR)))', [$user->id]);
            }
        }

        if ($request->ajax()) {
            $columns = Schema::getColumnListing('tasks');

            foreach ($request->all() as $key => $value) {
                if (!request()->filled($key) || !in_array($key, $columns)) {
                    continue;
                }

                if ($key === 'created_at') {
                    if($value == 1){
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    } elseif($value == 2){
                        $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    } elseif($value == 3 && $request->filled('created_at_range')){
                        $range = $request->created_at_range;
                        if (str_contains($range, ' to ')) {
                            $dates = explode(' to ', $range);
                            $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
                            $endDate = \Carbon\Carbon::parse($dates[1])->endOfDay();
                            $query->whereBetween('created_at', [$startDate, $endDate]);
                        } else {
                            $query->whereDate('created_at', \Carbon\Carbon::parse($range));
                        }
                    }
                } elseif ($key === 'assign_to') {
                    $query->whereRaw('JSON_CONTAINS(assign_to, JSON_QUOTE(CAST(? AS CHAR)))', [$value]);
                } else {
                    // This now works because $query is a Builder, not a Collection
                    $query->where($key, $value);
                }
            }
            // NOW execute the query
            $tasks = $query->get(); 
            
            return view('tasks.partials.row', compact('tasks'))->render();
        }

        $tasks = $query->get(); 
        $statusCounts = $tasks->groupBy('status')->map->count();
        $assignedUsers = User::whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('tasks')
                ->whereRaw('JSON_CONTAINS(tasks.assign_to, JSON_QUOTE(CAST(users.id AS CHAR)))')
                ->whereNull('deleted_at'); 
        })->select('id', 'name')->get();
        return view('tasks.index', compact('tasks', 'statusCounts', 'assignedUsers'));
    }

    
    // Fetch all tasks
    public function create()
    {
        $this->authorize('create tasks');
        $leads = Lead::select('id', 'company_name')->orderBy('company_name', 'asc')->get();
        $users = User::select('id', 'name')->orderBy('name', 'asc')->get();
        $task = new Task();
        $notes = new TaskNote();
        return view('tasks.manage_task', compact('leads', 'users', 'task', 'notes'));
    }

    // Create a new task
    public function edit(Task $task)
    {
        $this->authorize('edit tasks');
        $leads = Lead::select('id', 'company_name')->orderBy('company_name', 'asc')->get();
        $assignedUserIds = $task->assign_to ? array_map('intval', $task->assign_to) : [];
        $notes = TaskNote::with(['user:id,name'])->where('task_id', $task->id)->orderBy('created_at', 'desc')->get();
        return view('tasks.manage_task', compact('task', 'leads', 'assignedUserIds', 'notes'));
    }

    // Fetch by Lead ID
    public function getByLead($leadId)
    {
        return Task::where('lead_id', $leadId)->get();
    }

    // Fetch by User ID (Assigned To)
    public function getByUser($userId)
    {
        return Task::where('assign_to', $userId)->get();
    }

    public function manage_task(Request $request, Task $task = null)
    {
        $user = auth()->user();

        if ($request->input('_action') === 'delete') {
            $this->authorize('delete tasks');
            try {
                log_timeline($task->lead_id, null, null, 'task_deleted', $task->id);
                $task->delete();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true, 
                        'message' => 'Task deleted successfully.'
                    ]);
                }
                return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went wrong while deleting.');
            }
        }

        if ($request->input('form_status') === 'active') {
            $this->authorize('create tasks');
            $validated = $request->validate([
                'title'              => 'required|string|max:255',
                'lead_id'            => 'nullable|exists:leads,id',
                'type'               => 'required',
                'end_date'           => 'nullable|date',
                'description'        => 'nullable|string',
                'status'             => 'required',
                'priority'           => 'required',
                'assigned_manager'   => 'nullable|array',
                'assigned_executive' => 'nullable|array',
            ]);

            $assignedUsers = array_filter(array_merge(
                (array)$request->assigned_manager, 
                (array)$request->assigned_executive
            ));

            $data = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'type'        => $validated['type'],
                'lead_id'     => $validated['lead_id'],
                'assign_to'   => array_values($assignedUsers),
                'assign_by'   => auth()->id(),
                'end_date'    => $validated['end_date'],
                'priority'    => $validated['priority'],
                'status'      => $validated['status'],
                'others'      => null,
            ];
            if ($task && $task->exists) {
                $task->update($data);
            } else {
                $task = Task::create($data); 
            }
            $task && $task->exists ? $task->update($data) : Task::create($data);
            log_timeline($task->lead_id, $data, null, 'task_created', $task->id);
            return redirect()->route('tasks.index')->with('success', 'Task Saved successfully!');

        }

        if ($request->input('form_status') === 'update') {
            $this->authorize('edit tasks');
            
            $validated = $request->validate([
                'title'              => 'required|string|max:255',
                'lead_id'            => 'nullable|exists:leads,id',
                'type'               => 'required',
                'end_date'           => 'nullable|date',
                'description'        => 'nullable|string',
                'status'             => 'required',
                'priority'           => 'required',
                'assigned_manager'   => 'nullable|array',
                'assigned_executive' => 'nullable|array',
            ]);

            $data = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'type'        => $validated['type'],
                'lead_id'     => $validated['lead_id'],
                'assign_by'   => auth()->id(),
                'end_date'    => $validated['end_date'],
                'priority'    => $validated['priority'],
                'status'      => $validated['status'],
                'others'      => null,
            ];

            $shouldUpdateAssignments = true;
            if (!is_superadmin() && $user->can('view-own tasks')) {
                $shouldUpdateAssignments = false;
            }
            if ($shouldUpdateAssignments) {
                $assignedUsers = array_filter(array_merge(
                    (array)$request->assigned_manager, 
                    (array)$request->assigned_executive
                ));
                $data['assign_to'] = array_values($assignedUsers);
            }

            $task->update($data);
            log_timeline($task->lead_id, $data, null, 'task_updated', $task->id);
            return redirect()->route('tasks.edit', $task)->with('success', 'Task updated successfully.');
        }
        return redirect()->back()->with('error', 'Invalid form submission.');   
    }

    public function FetchTask($id){
        $task = Task::findOrFail($id);
        $notes = TaskNote::with(['user:id,name'])->where('task_id', $id)->orderBy('created_at', 'desc')->get();
        $html = view('tasks.partials.taskedit', compact('task', 'notes'))->render();
       
        return response()->json([
            'html' => $html
        ]);
    }

    public function storeNote(Request $request, $taskId)
    {
        $request->validate([
            'data' => 'required|string',
            'mentioned_id' => 'nullable|exists:users,id',
            'others' => 'nullable|string',
        ]);

        $note = TaskNote::create([
            'task_id'      => $taskId,
            'user_id'      => auth()->id(), // Assumes user is logged in
            'data'         => $request->data,
            'mentioned_id' => $request->mentioned_id,
            'others'       => $request->others,
        ]);

        $task = Task::findOrFail($taskId);
        $notes = TaskNote::with(['user:id,name'])->where('task_id', $taskId)->orderBy('created_at', 'desc')->get();
        $html = view('tasks.partials.taskedit', compact('task', 'notes'))->render();
       
        return response()->json([
            'message' => 'Note added successfully!',
            'html' => $html
        ]);
    }

    public function updateStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|string' // You can add 'in:pending,completed' if you have fixed statuses
        ]);

        $task = Task::findOrFail($taskId);
        
        // Update only the status column
        $task->update([
            'status' => $request->status
        ]);

        
        log_timeline($task->lead_id, $request->all(), null, 'task_status_updated', $task->id);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $request->status
        ]);
    }

    public function AddNote(Request $request, $taskId)
    {
        $request->validate([
            'data' => 'required|string', // Matches JS body
        ]);

        $note = TaskNote::create([
            'task_id'      => $taskId,
            'user_id'      => auth()->id(),
            'data'         => $request->data,
        ]);

        $notes = TaskNote::with(['user:id,name'])->where('task_id', $taskId)->orderBy('created_at', 'desc')->get();
        $html = view('tasks.partials.task_per_row', compact('notes'))->render();
        
        return response()->json([
            'success' => true, // Needed for JS if(data.success)
            'message' => 'Note added successfully!',
            'html' => $html
        ]);
    }

    public function Schedulecreate(Request $request)
    {
        // Use the field name coming from your AJAX (follow_up_date)
        $date = $request->end_date;

        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Date is required']);
        }

        $allTypes = taskTypes();
        $allPriorities = taskPriorities();
        $allStatus = tasksStatus();

        try {
            $task = new Task();
            
            $task->lead_id      = $request->lead_id;
            $task->title        = 'Follow Up Scheduled';
            $task->type         = isset($allTypes['follow-up']) ? 'follow-up' : array_key_first($allTypes);
            $task->status       = array_key_first($allStatus); // 'pending'
            $task->priority     = array_key_first($allPriorities);
            $task->end_date     = $date;
            $task->is_scheduled = 1;
            $task->assign_to = [auth()->id()];
            $task->reminder     = $request->reminder == 1 ? 1 : 0;
            $task->assign_by    = auth()->id();
            
            $task->save();

            log_timeline($request->lead_id, $request->all(), null, 'task_created', $task->id);

            return response()->json([
                'success' => true, 
                'message' => 'Follow-up scheduled'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}