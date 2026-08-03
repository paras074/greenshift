<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PriorityStatus;
use Illuminate\Http\Request;

class PriorityStatusController extends Controller
{
    public function index()
    {
        $this->authorize('view priority-statuses');
        $priorities = PriorityStatus::orderBy('sort_order')->orderBy('name')->paginate(10);
        return view('settings.priority-statuses.index', compact('priorities'));
    }

    public function create()
    {
        $this->authorize('create priority-statuses');
        return view('settings.priority-statuses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create priority-statuses');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:priority_statuses,name',
            'color'      => 'required|string|max:7',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        PriorityStatus::create($validated);

        return redirect()->route('settings.priority-statuses.index')->with('success', "Priority '{$validated['name']}' created successfully.");
    }

    public function edit(PriorityStatus $priorityStatus)
    {
        $this->authorize('edit priority-statuses');
        return view('settings.priority-statuses.edit', compact('priorityStatus'));
    }

    public function update(Request $request, PriorityStatus $priorityStatus)
    {
        $this->authorize('edit priority-statuses');

        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:priority_statuses,name,' . $priorityStatus->id,
            'color'      => 'required|string|max:7',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $priorityStatus->update($validated);

        return redirect()->route('settings.priority-statuses.index')->with('success', "Priority '{$priorityStatus->name}' updated successfully.");
    }

    public function destroy(PriorityStatus $priorityStatus)
    {
        $this->authorize('delete priority-statuses');
        $priorityStatus->delete();

        return redirect()->route('settings.priority-statuses.index')->with('success', "Priority deleted successfully.");
    }

    public function toggleStatus(PriorityStatus $priorityStatus)
    {
        $this->authorize('edit priority-statuses');
        $priorityStatus->update([
            'status' => $priorityStatus->status === 'active' ? 'inactive' : 'active'
        ]);
        return back()->with('success', "Status updated.");
    }
}