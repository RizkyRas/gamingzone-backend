<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'nama',
        'tipe',
        'harga_per_jam',
        'deskripsi',
        'fasilitas',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'fasilitas' => 'array',
        'is_active' => 'boolean',
        'harga_per_jam' => 'float',
    ];
}

