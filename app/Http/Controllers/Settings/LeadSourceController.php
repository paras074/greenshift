<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index()
    {
        $this->authorize('view lead-sources');
        $sources = LeadSource::orderBy('sort_order')->orderBy('name')->paginate(10);
        return view('settings.lead-sources.index', compact('sources'));
    }

    public function create()
    {
        $this->authorize('create lead-sources');
        return view('settings.lead-sources.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create lead-sources');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:lead_sources,name',
            'icon'       => 'nullable|string|max:100',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        LeadSource::create($validated);

        return redirect()->route('settings.lead-sources.index')->with('success', "Lead Source '{$validated['name']}' created successfully.");
    }

    public function edit(LeadSource $leadSource)
    {
        $this->authorize('edit lead-sources');
        return view('settings.lead-sources.edit', compact('leadSource'));
    }

    public function update(Request $request, LeadSource $leadSource)
    {
        $this->authorize('edit lead-sources');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:lead_sources,name,' . $leadSource->id,
            'icon'       => 'nullable|string|max:100',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $leadSource->update($validated);

        return redirect()->route('settings.lead-sources.index')->with('success', "Lead Source '{$leadSource->name}' updated successfully.");
    }

    public function destroy(LeadSource $leadSource)
    {
        $this->authorize('delete lead-sources');
        $leadSource->delete();

        return redirect()->route('settings.lead-sources.index')->with('success', "Lead Source deleted successfully.");
    }

    public function toggleStatus(LeadSource $leadSource)
    {
        $this->authorize('edit lead-sources');
        $leadSource->update([
            'status' => $leadSource->status === 'active' ? 'inactive' : 'active'
        ]);
        return back()->with('success', "Status updated.");
    }
}