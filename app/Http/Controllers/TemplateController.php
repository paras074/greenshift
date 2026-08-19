<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $this->authorize('view templates');
        $templates = Template::orderBy('type')->orderByDesc('is_active')->orderBy('name')->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create templates');
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create templates');

        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'type'      => 'required|in:loa,email',
            'subject'   => 'nullable|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $template = Template::create($validated);

        // Only one active template per type.
        if ($template->is_active) {
            $this->makeActive($template);
        }

        return redirect()->route('templates.index')->with('success', "Template '{$template->name}' created successfully.");
    }

    public function edit(Template $template)
    {
        $this->authorize('edit templates');
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $this->authorize('edit templates');

        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'type'      => 'required|in:loa,email',
            'subject'   => 'nullable|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $template->update($validated);

        if ($template->is_active) {
            $this->makeActive($template);
        }

        return redirect()->route('templates.index')->with('success', "Template '{$template->name}' updated successfully.");
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete templates');
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }

    public function setActive(Template $template)
    {
        $this->authorize('edit templates');
        $this->makeActive($template);

        return back()->with('success', "'{$template->name}' is now the active {$template->type} template.");
    }

    /**
     * Mark a template active and deactivate every other template of the same type.
     */
    private function makeActive(Template $template): void
    {
        Template::where('type', $template->type)
            ->where('id', '!=', $template->id)
            ->update(['is_active' => false]);

        $template->update(['is_active' => true]);
    }
}
