<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::with('room')->orderBy('id', 'desc');

        if ($user && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'nama_pemesan' => 'required|string',
            'email_pemesan' => 'nullable|email',
            'no_hp_pemesan' => 'nullable|string',
            'tanggal_booking' => 'required|date',
            'jam_mulai' => 'required|string',
            'durasi_jam' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Hitung jam selesai
        $startHour = (int) explode(':', $validated['jam_mulai'])[0];
        $startMinute = explode(':', $validated['jam_mulai'])[1] ?? '00';
        $endHour = ($startHour + $validated['durasi_jam']) % 24;
        $jamSelesai = sprintf('%02d:%s', $endHour, $startMinute);

        // Validasi Konflik Jadwal (Double Booking Protection)
        $conflict = Booking::where('room_id', $room->id)
            ->where('tanggal_booking', $validated['tanggal_booking'])
            ->whereIn('status', ['dikonfirmasi', 'pending_payment'])
            ->where(function ($q) use ($validated, $jamSelesai) {
                $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $jamSelesai])
                  ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $jamSelesai]);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bentrok! Room ini sudah dibooking pada jam tersebut.'
            ], 422);
        }

        $totalHarga = $room->harga_per_jam * $validated['durasi_jam'];
        $dp = round($totalHarga * 0.3); // DP 30%
        $sisa = $totalHarga - $dp;
        $noInvoice = 'INV-ROOM-' . strtoupper(uniqid());

        $user = $request->user();

        $booking = Booking::create([
            'no_invoice' => $noInvoice,
            'user_id' => $user ? $user->id : null,
            'room_id' => $room->id,
            'nama_pemesan' => $validated['nama_pemesan'],
            'email_pemesan' => $validated['email_pemesan'] ?? ($user ? $user->email : null),
            'no_hp_pemesan' => $validated['no_hp_pemesan'] ?? ($user ? $user->no_hp : null),
            'tanggal_booking' => $validated['tanggal_booking'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $jamSelesai,
            'durasi_jam' => $validated['durasi_jam'],
            'total_harga' => $totalHarga,
            'dp_dibayar' => $dp,
            'sisa_bayar' => $sisa,
            'status' => 'pending_payment',
            'status_pembayaran' => 'belum_bayar',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi booking berhasil dibuat, silakan lanjutkan pembayaran DP.',
            'data' => $booking->load('room')
        ], 201);
    }

    public function show($id)
    {
        $booking = Booking::with('room')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:pending_payment,dikonfirmasi,selesai,dibatalkan',
            'status_pembayaran' => 'nullable|string|in:belum_bayar,dp,lunas',
        ]);

        $booking->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status booking berhasil diperbarui',
            'data' => $booking
        ]);
    }
}

