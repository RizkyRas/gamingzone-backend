<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get()->map(function ($u) {
            return [
                'id' => (string) $u->id,
                'nama' => $u->name,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role ?? 'customer',
                'no_hp' => $u->no_hp ?? '-',
                'nomorHP' => $u->no_hp ?? '-',
                'aktif' => isset($u->aktif) ? (bool) $u->aktif : true,
                'createdAt' => $u->created_at ? $u->created_at->toIso8601String() : now()->toIso8601String(),
                'created_at' => $u->created_at ? $u->created_at->toIso8601String() : now()->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil',
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
            'no_hp' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'customer',
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user (e.g. toggle aktif, update role, etc.).
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($request->has('role')) {
            $user->role = $request->role;
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('no_hp')) {
            $user->no_hp = $request->no_hp;
        }

        if ($request->has('aktif') && Schema::hasColumn('users', 'aktif')) {
            $user->aktif = $request->aktif;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diperbarui',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}

