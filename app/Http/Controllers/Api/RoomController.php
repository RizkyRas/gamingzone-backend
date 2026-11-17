<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::where('is_active', true);

        if ($request->has('tipe') && $request->tipe != 'Semua') {
            $query->where('tipe', $request->tipe);
        }

        $rooms = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar room berhasil diambil',
            'data' => $rooms
        ]);
    }

    public function show($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }
}

