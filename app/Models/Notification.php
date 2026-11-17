<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'target',
        'judul',
        'pesan',
        'tipe',
        'ref_id',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];
}

