<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    
    // ── List all users ────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('view users');

        $query = User::with('roles')->latest();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    // ── Show create form ──────────────────────────────────
    public function create()
    {
        $this->authorize('create users');

        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    // ── Store new user ────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('create users');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
            'status'   => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status'   => $validated['status'],
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    // ── Show edit form ────────────────────────────────────
    public function edit(User $user)
    {
        $this->authorize('edit users');

        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    // ── Update user ───────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $this->authorize('edit users');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
            'status'   => 'required|in:active,inactive',
        ]);

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'status'   => $validated['status'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    // ── Delete user ───────────────────────────────────────
    public function destroy(User $user)
    {
        $this->authorize('delete users');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', "User '{$user->name}' deleted successfully.");
    }

    // ── Toggle status ─────────────────────────────────────
    public function toggleStatus(User $user)
    {
        $this->authorize('edit users');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        return back()->with('success', "User status updated.");
    }
}