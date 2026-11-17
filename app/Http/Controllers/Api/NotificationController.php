<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $target = $request->query('target', 'user');

        $query = Notification::orderBy('id', 'desc');

        if ($target === 'admin' || ($user && $user->role === 'admin')) {
            $query->where('target', 'admin');
        } else {
            $query->where('target', 'user');
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['dibaca' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $target = $request->query('target', 'user');

        $query = Notification::where('dibaca', false);

        if ($target === 'admin' || ($user && $user->role === 'admin')) {
            $query->where('target', 'admin');
        } else {
            $query->where('target', 'user');
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        $query->update(['dibaca' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ]);
    }
}

