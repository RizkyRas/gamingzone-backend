<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'deskripsi',
        'image_url',
        'is_ready',
    ];

    protected $casts = [
        'is_ready' => 'boolean',
        'harga' => 'float',
    ];
}

