<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id'      => 'required|exists:leads,id',
            'data'         => 'required|string',
            'mentioned_id' => 'nullable|exists:users,id',
            'others'       => 'nullable|string',
        ]);

        $note = Note::create([
            'lead_id'      => $validated['lead_id'],
            'user_id'      => Auth::id(),
            'data'         => $validated['data'],
            'mentioned_id' => $validated['mentioned_id'] ?? null,
            'others'       => $validated['others'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully!',
            'note'    => $note->load('user:id,name')
        ]);
    }

    public function getNotes(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id'
        ]);

        $notes = Note::with(['user:id,name', 'mentionedUser:id,name'])
            ->where('lead_id', $request->lead_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    public function searchUsers(Request $request)
    {
        // here we can implement a search functionality to find users by name or email
    }
}