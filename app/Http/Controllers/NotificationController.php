<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $query = Notification::with(['lead', 'sender'])
            ->latest();

        if (is_superadmin()) {
            $query->where(function ($q) use ($userId) {
                $q->where('to', $userId)
                ->orWhere('to', 0);
            });
        } else {
            $query->where('to', $userId);
        }

        $unreadCount = (clone $query)
            ->where('is_read', 0)
            ->count();

        $notifications = $query->get();

        return response()->json([
            'html' => view('layouts.notifications-list', compact('notifications'))->render(),
            'count' => $unreadCount
        ]);
    }

    public function markRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => 1
        ]);

        return response()->json(['success' => true]);
    }
}