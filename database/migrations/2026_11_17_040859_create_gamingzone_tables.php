<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update tabel users (tambahkan role, no_hp, dll)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('customer')->after('password'); // 'admin' atau 'customer'
            }
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp')->nullable()->after('role');
            }
        });

        // 2. Tabel Rooms (Kamar Gaming PS5, PC Simulator, dll)
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: 'VIP 1', 'VIP 2', 'VVIP 1', 'Simulator 1'
            $table->string('tipe'); // 'VIP', 'VVIP', 'Reguler', 'Simulator'
            $table->decimal('harga_per_jam', 12, 2);
            $table->text('deskripsi')->nullable();
            $table->json('fasilitas')->nullable(); // array fasilitas ['PS5', 'XBOX', 'Netflix']
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel Bookings (Reservasi Room)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('nama_pemesan');
            $table->string('email_pemesan')->nullable();
            $table->string('no_hp_pemesan')->nullable();
            $table->date('tanggal_booking');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_jam');
            $table->decimal('total_harga', 12, 2);
            $table->decimal('dp_dibayar', 12, 2)->default(0);
            $table->decimal('sisa_bayar', 12, 2)->default(0);
            $table->string('status')->default('pending_payment'); // pending_payment, dikonfirmasi, selesai, dibatalkan
            $table->string('status_pembayaran')->default('belum_bayar'); // belum_bayar, dp, lunas
            $table->text('catatan')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Menus (Menu Cafe Makanan & Minuman)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori'); // 'Makanan', 'Minuman', 'Snack'
            $table->decimal('harga', 12, 2);
            $table->text('deskripsi')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_ready')->default(true);
            $table->timestamps();
        });

        // 5. Tabel Orders (Pesanan Cafe)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pemesan');
            $table->string('nomor_meja'); // contoh: 'VIP 1', 'Meja Cafe 02'
            $table->decimal('total_harga', 12, 2);
            $table->string('status')->default('menunggu'); // menunggu, diproses, selesai, dibatalkan
            $table->string('status_pembayaran')->default('belum_bayar'); // belum_bayar, lunas
            $table->string('midtrans_order_id')->nullable();
            $table->timestamps();
        });

        // 6. Tabel Order Items (Detail item cafe)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('nama_menu');
            $table->decimal('harga', 12, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 7. Tabel Payments (Catatan transaksi Midtrans)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // e.g. BOOKING-101-179488
            $table->string('tipe_transaksi'); // 'booking' atau 'cafe'
            $table->unsignedBigInteger('referensi_id'); // booking_id atau order_id
            $table->decimal('gross_amount', 12, 2);
            $table->string('payment_type')->nullable(); // qris, gopay, bank_transfer
            $table->string('transaction_status')->default('pending'); // pending, settlement, cancel, expire
            $table->string('snap_token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('payload_response')->nullable();
            $table->timestamps();
        });

        // 8. Tabel Notifications (Notifikasi User & Admin)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // null = notif admin
            $table->string('target')->default('user'); // 'user' atau 'admin'
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe'); // 'booking_baru', 'pembayaran_berhasil', 'pesanan_cafe_baru'
            $table->string('ref_id')->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('rooms');
    }
};
