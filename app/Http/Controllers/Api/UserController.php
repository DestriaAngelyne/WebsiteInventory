<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Admin: lihat semua user (siswa)
    public function index()
    {
        $users = User::where('role', 'siswa')->latest()->get();

        return response()->json($users);
    }

    // Admin: hapus akun siswa
    public function destroy(User $user, Request $request)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Tidak bisa menghapus akun admin.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }
}
