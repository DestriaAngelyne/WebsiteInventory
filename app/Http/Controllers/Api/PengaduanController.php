<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;

class PengaduanController extends Controller
{
    // Siswa: lihat pengaduan miliknya sendiri
    public function myPengaduan(Request $request)
    {
        $pengaduan = Pengaduan::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($pengaduan);
    }

    // Siswa: buat pengaduan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subjek' => ['required', 'string', 'max:255'],
            'pesan' => ['required', 'string', 'max:1000'],
            'peminjaman_id' => ['nullable', 'exists:peminjaman,id'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'belum_dibaca';

        $pengaduan = Pengaduan::create($validated);

        return response()->json($pengaduan, 201);
    }

    // Admin: lihat semua pengaduan
    public function index()
    {
        $pengaduan = Pengaduan::with('user')->latest()->get();

        return response()->json($pengaduan);
    }

    // Admin: lihat detail 1 pengaduan (otomatis tandai "diproses" kalau masih "belum_dibaca")
    public function show(Pengaduan $pengaduan)
    {
        if ($pengaduan->status === 'belum_dibaca') {
            $pengaduan->update(['status' => 'diproses']);
        }

        return response()->json($pengaduan->load('user'));
    }

    // Admin: tandai selesai + kasih tanggapan
    public function selesaikan(Pengaduan $pengaduan, Request $request)
    {
        $validated = $request->validate([
            'tanggapan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $pengaduan->update([
            'status' => 'selesai',
            'tanggapan_admin' => $validated['tanggapan_admin'] ?? $pengaduan->tanggapan_admin,
        ]);

        return response()->json($pengaduan->load('user'));
    }
}
