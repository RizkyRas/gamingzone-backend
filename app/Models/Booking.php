<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'no_invoice',
        'user_id',
        'room_id',
        'nama_pemesan',
        'email_pemesan',
        'no_hp_pemesan',
        'tanggal_booking',
        'jam_mulai',
        'jam_selesai',
        'durasi_jam',
        'total_harga',
        'dp_dibayar',
        'sisa_bayar',
        'status',
        'status_pembayaran',
        'catatan',
        'midtrans_order_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

