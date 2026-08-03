<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    public function index()
    {
        $this->authorize('view lead-statuses');
        $statuses = LeadStatus::orderBy('sort_order')->orderBy('name')->paginate(10);
        return view('settings.lead-statuses.index', compact('statuses'));
    }

    public function create()
    {
        $this->authorize('create lead-statuses');
        return view('settings.lead-statuses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create lead-statuses');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:lead_statuses,name',
            'color'      => 'required|string|max:7',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'show_kanban' => 'required|boolean',
        ]);

        LeadStatus::create($validated);

        return redirect()->route('settings.lead-statuses.index')->with('success', "Lead Status '{$validated['name']}' created successfully.");
    }

    public function edit(LeadStatus $leadStatus)
    {
        $this->authorize('edit settings');
        return view('settings.lead-statuses.edit', compact('leadStatus'));
    }

    public function update(Request $request, LeadStatus $leadStatus)
    {
        $this->authorize('edit lead-statuses');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:lead_statuses,name,' . $leadStatus->id,
            'color'      => 'required|string|max:7',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'show_kanban' => 'required|boolean',
        ]);

        $leadStatus->update($validated);

        return redirect()->route('settings.lead-statuses.index')->with('success', "Lead Status '{$leadStatus->name}' updated successfully.");
    }

    public function destroy(LeadStatus $leadStatus)
    {
        $this->authorize('delete lead-statuses');
        $leadStatus->delete();

        return redirect()->route('settings.lead-statuses.index')->with('success', "Lead Status deleted successfully.");
    }

    public function toggleStatus(LeadStatus $leadStatus)
    {
        $this->authorize('edit lead-statuses');
        $leadStatus->update([
            'status' => $leadStatus->status === 'active' ? 'inactive' : 'active'
        ]);
        return back()->with('success', "Status updated.");
    }
}