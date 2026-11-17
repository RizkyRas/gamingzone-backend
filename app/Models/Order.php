<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'no_invoice',
        'user_id',
        'nama_pemesan',
        'nomor_meja',
        'total_harga',
        'status',
        'status_pembayaran',
        'midtrans_order_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

