<?php

namespace App\Http\Controllers;

use App\Models\LeadStep;
use Illuminate\Http\Request;

class LeadStepController extends Controller
{
    // List all lead steps
    public function index()
    {
        $this->authorize('view manage-lead-funnel');
        $steps = LeadStep::orderBy('sort_order', 'asc')->get();
        return view('settings.lead_steps.index', compact('steps'));
    }

    // Show create form
    public function create()
    {
        $this->authorize('view manage-lead-funnel');
        return view('settings.lead_steps.create');
    }

    // Store new lead step
    public function store(Request $request)
    {
        $this->authorize('view manage-lead-funnel');
        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:0,1'
        ]);

        // 2. Get the next sort order (current count + 1)
        // Or use LeadStep::max('sort_order') + 1 if you expect deletions
        $nextSortOrder = LeadStep::count() + 1;

        // 3. Create the record
        LeadStep::create([
            'name'       => $request->name,
            'status'     => $request->status,
            'sort_order' => $nextSortOrder,
        ]);

        return redirect()->route('settings.lead-steps.index')->with('success', 'Step created successfully.');
    }

    // Show edit form
    public function edit(LeadStep $leadStep)
    {
        $this->authorize('view manage-lead-funnel');
        return view('settings.lead_steps.edit', compact('leadStep'));
    }

    // Update lead step
    public function update(Request $request, LeadStep $leadStep)
    {
        $this->authorize('view manage-lead-funnel');
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1'
        ]);

        $leadStep->update($request->all());

        return redirect()->route('settings.lead-steps.index')->with('success', 'Step updated successfully.');
    }

    public function reorder(Request $request)
    {
        $this->authorize('view manage-lead-funnel');
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:lead_steps,id',
            'order.*.position' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            \App\Models\LeadStep::where('id', $item['id'])->update([
                'sort_order' => $item['position']
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Order updated successfully']);
    }

    public function destroy(LeadStep $leadStep)
    {
        $this->authorize('view manage-lead-funnel');
        $leadStep->delete();
        return redirect()->route('settings.lead-steps.index')->with('success', 'Step deleted successfully.');
    }
}