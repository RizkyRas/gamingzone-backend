<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin & Customer Awal
        User::updateOrCreate(
            ['email' => 'admin@gamingzone.id'],
            [
                'name' => 'Administrator Gaming Zone',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'no_hp' => '081234567890'
            ]
        );

        User::updateOrCreate(
            ['email' => 'rizkyanugrah@gmail.com'],
            [
                'name' => 'Rizky Anugrah Surbakti',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'no_hp' => '082167890123'
            ]
        );

        // 2. Data Rooms (Sesuai Firestore & Mobile)
        $rooms = [
            [
                'id' => 1,
                'nama' => 'VIP 1 (VIP)',
                'tipe' => 'VIP',
                'harga_per_jam' => 50000,
                'deskripsi' => 'Ruang VIP 1 ber-AC dengan layar TV 55 inch 4K, sofa santai, soundbar premium, dan console PS5 lengkap 2 stik.',
                'fasilitas' => json_encode(['PS5', 'Netflix', 'Sofa Bed', 'Air Conditioner', 'Soundbar']),
                'image_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'VIP 2 (VIP)',
                'tipe' => 'VIP',
                'harga_per_jam' => 55000,
                'deskripsi' => 'Ruang VIP 2 dengan TV 65 inch OLED, PS5 Pro + XBOX Series X, 2 stik dualsense, dan mini snack bar.',
                'fasilitas' => json_encode(['PS5', 'XBOX', 'Netflix', 'Mini Bar', 'Air Conditioner']),
                'image_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama' => 'VVIP Sultan 1',
                'tipe' => 'VVIP',
                'harga_per_jam' => 85000,
                'deskripsi' => 'Ruang private VVIP super luas, TV 75 inch 120Hz, PS5, Nintendo Switch OLED, free soft drink & popcorn.',
                'fasilitas' => json_encode(['PS5', 'Nintendo Switch', 'Private Room', 'Free Snack', 'Reclining Sofa']),
                'image_url' => 'https://images.unsplash.com/photo-1547394765-185e1e68f34e?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nama' => 'Simulator Racing F1',
                'tipe' => 'Simulator',
                'harga_per_jam' => 75000,
                'deskripsi' => 'Cockpit balap presisi tinggi dengan steering wheel Fanatec Direct Drive, load-cell pedals, dan triple curved monitor.',
                'fasilitas' => json_encode(['Fanatec DD', 'Triple Monitor', 'Racing Cockpit', 'H-Shifter', 'VR Headset']),
                'image_url' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($rooms as $r) {
            DB::table('rooms')->updateOrInsert(['id' => $r['id']], $r);
        }

        // 3. Data Menu Cafe (Makanan & Minuman)
        $menus = [
            [
                'id' => 1,
                'nama' => 'French Fries Special',
                'kategori' => 'Makanan',
                'harga' => 20000,
                'deskripsi' => 'Kentang goreng renyah disajikan dengan taburan keju parmesan dan saus pedas manis istimewa.',
                'image_url' => 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=600&q=80',
                'is_ready' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'Double Blackberry Milk',
                'kategori' => 'Minuman',
                'harga' => 25000,
                'deskripsi' => 'Susu segar berpadu dengan sirup blackberry asli dan jelly kenyal menyegarkan.',
                'image_url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80',
                'is_ready' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama' => 'Double Match Milk',
                'kategori' => 'Minuman',
                'harga' => 25000,
                'deskripsi' => 'Matcha murni khas Jepang berpadu dengan creamy fresh milk yang lembut.',
                'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80',
                'is_ready' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nama' => 'Chicken Popcorn Spicy',
                'kategori' => 'Makanan',
                'harga' => 28000,
                'deskripsi' => 'Daging ayam fillet crispy ukuran gigitan dengan bumbu tabur pedas gurih.',
                'image_url' => 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=600&q=80',
                'is_ready' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($menus as $m) {
            DB::table('menus')->updateOrInsert(['id' => $m['id']], $m);
        }
    }
}
