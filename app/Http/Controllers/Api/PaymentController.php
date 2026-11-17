<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Notification;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:booking,cafe',
            'docId' => 'required',
            'grossAmount' => 'required|numeric|min:1',
            'customerName' => 'nullable|string',
            'customerEmail' => 'nullable|email',
        ]);

        $prefix = $validated['type'] === 'booking' ? 'BOOKING' : 'CAFE';
        $orderId = "{$prefix}-{$validated['docId']}-" . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($validated['grossAmount']),
            ],
            'customer_details' => [
                'first_name' => $validated['customerName'] ?: 'Pelanggan',
                'email' => $validated['customerEmail'] ?: 'guest@gamingzone.id',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $redirectUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

            return response()->json([
                'success' => true,
                'token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'orderId' => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $isSuccess = ($transactionStatus === 'capture' && $fraudStatus === 'accept') || $transactionStatus === 'settlement';

        if (str_starts_with($orderId, 'BOOKING-')) {
            $parts = explode('-', $orderId);
            $bookingId = $parts[1] ?? null;

            if ($bookingId && $booking = Booking::find($bookingId)) {
                if ($isSuccess) {
                    $booking->update([
                        'status' => 'dikonfirmasi',
                        'status_pembayaran' => 'dp',
                        'midtrans_order_id' => $orderId,
                    ]);

                    Notification::create([
                        'user_id' => $booking->user_id,
                        'target' => 'user',
                        'judul' => 'Pembayaran DP Berhasil ✅',
                        'pesan' => "Reservasi room {$booking->room?->nama} telah dikonfirmasi.",
                        'tipe' => 'pembayaran_berhasil',
                        'ref_id' => $booking->id,
                    ]);

                    Notification::create([
                        'target' => 'admin',
                        'judul' => '🎮 Booking Room Baru (Lunas DP)',
                        'pesan' => "{$booking->nama_pemesan} memesan {$booking->room?->nama}.",
                        'tipe' => 'booking_baru',
                        'ref_id' => $booking->id,
                    ]);
                }
            }
        } elseif (str_starts_with($orderId, 'CAFE-')) {
            $parts = explode('-', $orderId);
            $orderCafeId = $parts[1] ?? null;

            if ($orderCafeId && $order = Order::find($orderCafeId)) {
                if ($isSuccess) {
                    $order->update([
                        'status' => 'diproses',
                        'status_pembayaran' => 'lunas',
                        'midtrans_order_id' => $orderId,
                    ]);

                    Notification::create([
                        'user_id' => $order->user_id,
                        'target' => 'user',
                        'judul' => 'Pesanan Cafe Diterima 🍽️',
                        'pesan' => "Pesanan cafe kamu tujuan meja {$order->nomor_meja} sedang disiapkan dapur.",
                        'tipe' => 'pesanan_cafe_sukses',
                        'ref_id' => $order->id,
                    ]);

                    Notification::create([
                        'target' => 'admin',
                        'judul' => '🍽️ Pesanan Cafe Baru (Lunas)',
                        'pesan' => "{$order->nama_pemesan} memesan menu cafe (Meja: {$order->nomor_meja}).",
                        'tipe' => 'pesanan_cafe_baru',
                        'ref_id' => $order->id,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

