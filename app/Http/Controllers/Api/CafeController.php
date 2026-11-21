<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class CafeController extends Controller
{
    public function getMenus(Request $request)
    {
        $query = Menu::where('is_ready', true);

        if ($request->has('kategori') && $request->kategori != 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        $menus = $query->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }

    public function getOrders(Request $request)
    {
        $user = $request->user();
        $query = Order::with('items')->orderBy('id', 'desc');

        if ($user && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('nama_pemesan')) {
            $query->where('nama_pemesan', $request->nama_pemesan);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string',
            'nama_pemesan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($validated, $user, $request) {
            $totalHarga = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $menuId = isset($item['menu_id']) && is_numeric($item['menu_id']) ? intval($item['menu_id']) : null;
                $menu = $menuId ? Menu::find($menuId) : null;
                $nama = $menu ? $menu->nama : ($item['nama'] ?? $item['nama_menu'] ?? 'Menu');
                $harga = $menu ? $menu->harga : (isset($item['harga']) ? intval($item['harga']) : 25000);
                $subtotal = $harga * $item['qty'];
                $totalHarga += $subtotal;

                $itemsData[] = [
                    'menu_id' => $menu ? $menu->id : 1,
                    'nama_menu' => $nama,
                    'harga' => $harga,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'no_invoice' => $request->input('no_invoice') ?? ('INV-CAFE-' . strtoupper(uniqid())),
                'user_id' => $user ? $user->id : null,
                'nama_pemesan' => $validated['nama_pemesan'],
                'nomor_meja' => $validated['nomor_meja'],
                'total_harga' => $totalHarga,
                'status' => $request->input('status', 'diproses'),
                'status_pembayaran' => $request->input('status_pembayaran', 'lunas'),
            ]);

            foreach ($itemsData as $data) {
                $order->items()->create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan cafe berhasil dibuat',
                'data' => $order->load('items')
            ], 201);
        });
    }
}

