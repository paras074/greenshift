<?php

namespace App\Http\Controllers;

use App\Models\LeadAssignment;
use App\Models\Lead;
use Illuminate\Http\Request;


class LeadAssignmentController extends Controller
{
    public function store(Request $request)
    {
        // 1. Removed 'type' from validation
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $assignment = LeadAssignment::firstOrCreate($validated);

            log_timeline($validated['lead_id'], $validated['user_id'], null, 'member_assigned');

            return response()->json([
                'status'  => 'success',
                'message' => 'Lead assigned successfully.',
                'data'    => $assignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to assign lead.'
            ], 500);
        }
    }
    
    public function getByLead($leadId)
    {
        $assignments = LeadAssignment::with('user')
            ->where('lead_id', $leadId)
            ->get();

        return response()->json($assignments);
    }
    
    public function destroy($id)
    {
        $assignment = LeadAssignment::findOrFail($id);
        log_timeline($assignment->lead_id, $assignment->user_id, null, 'member_unassigned');
        $assignment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Assignment removed.'
        ]);
    }
    
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'user_ids.*' => 'required|exists:users,id',
        ]);

        $assignments = [];
        foreach ($validated['lead_ids'] as $leadId) {
            LeadAssignment::where('lead_id', $leadId)->delete();
            foreach ($validated['user_ids'] as $userId) {
                $assignment = LeadAssignment::firstOrCreate([
                    'lead_id' => $leadId,
                    'user_id' => $userId,
                ]);
                $assignments[] = $assignment;
            }
            log_timeline($leadId, $validated['user_ids'], null, 'member_assigned_bulk');
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'User assigned to Leads successfully.',
            'data'    => $assignments
        ], 201);
    }
}