<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // ── List all roles ────────────────────────────────────
    public function index()
    {
        $this->authorize('view roles');

        $roles = Role::withCount('users', 'permissions')->latest()->get();

        return view('roles.index', compact('roles'));
    }

    // ── Show create form ──────────────────────────────────
    public function create()
    {
        $this->authorize('create roles');

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return end($parts) ?? 'general';
        });

        return view('roles.create', compact('permissions'));
    }

    // ── Store new role ────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create roles');

        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $validated['name']))
        ]);

        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $prettyName = ucwords(str_replace('_', ' ', $role->name));

        return redirect()->route('roles.index')->with('success', "Role '{$prettyName}' created successfully.");
    }

    // ── Show edit form ────────────────────────────────────
    public function edit(Role $role)
    {
        $this->authorize('edit roles');

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return end($parts) ?? 'general';
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // ── Update role ───────────────────────────────────────
    public function update(Request $request, Role $role)
    {
        $this->authorize('edit roles');

        if ($role->name === 'superadmin') {
            return back()->with('error', 'Superadmin role cannot be modified.');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => strtolower(str_replace(' ', '_', $validated['name']))
        ]);

        $permissions = !empty($validated['permissions'])
            ? Permission::whereIn('id', $validated['permissions'])->get()
            : [];

        $role->syncPermissions($permissions);

        $prettyName = ucwords(str_replace('_', ' ', $role->name));

        return redirect()->route('roles.index')->with('success', "Role '{$prettyName}' updated successfully.");
    }

    // ── Delete role ───────────────────────────────────────
    public function destroy(Role $role)
    {
        $this->authorize('delete roles');

        if (in_array($role->name, ['superadmin', 'admin'])) {
            return back()->with('error', "The '{$role->name}' role cannot be deleted.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete role with assigned users. Reassign users first.");
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role deleted successfully.");
    }

    // ── Show role detail with users & permissions ─────────
    public function show(Role $role)
    {
        $this->authorize('view roles');

        $role->load('permissions', 'users');

        $permissions = $role->permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return end($parts) ?? 'general';
        });

        return view('roles.show', compact('role', 'permissions'));
    }
}