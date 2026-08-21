<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    // Siswa: lihat riwayat peminjaman miliknya sendiri
    public function myPeminjaman(Request $request)
    {
        $peminjaman = Peminjaman::with('barang')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($peminjaman);
    }

    // Siswa: ajukan peminjaman baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $peminjaman = Peminjaman::create($validated);

        return response()->json($peminjaman->load('barang'), 201);
    }

    // Siswa: batalkan pengajuan yang masih pending
    public function batalkan(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($peminjaman->status !== 'pending') {
            return response()->json(['message' => 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.'], 422);
        }

        $peminjaman->update(['status' => 'dibatalkan']);

        return response()->json($peminjaman->load('barang'));
    }

    // Siswa: minta perpanjangan
    public function mintaPerpanjangan(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($peminjaman->status !== 'disetujui') {
            return response()->json(['message' => 'Perpanjangan hanya bisa diminta untuk peminjaman yang sedang berjalan.'], 422);
        }

        $validated = $request->validate([
            'tanggal_kembali_diminta' => ['required', 'date', 'after:tanggal_kembali_rencana'],
        ]);

        $peminjaman->update([
            'tanggal_kembali_diminta' => $validated['tanggal_kembali_diminta'],
            'status_perpanjangan' => 'pending',
        ]);

        return response()->json($peminjaman->load('barang'));
    }

    // Admin: lihat semua pengajuan peminjaman
    public function index()
    {
        $peminjaman = Peminjaman::with(['barang', 'user'])->latest()->get();

        return response()->json($peminjaman);
    }

    // Admin: setujui pengajuan
    public function approve(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->status !== 'pending') {
            return response()->json(['message' => 'Peminjaman ini sudah diproses.'], 422);
        }

        if ($peminjaman->barang->stok < $peminjaman->jumlah) {
            return response()->json(['message' => 'Stok barang tidak mencukupi.'], 422);
        }

        $peminjaman->barang->decrement('stok', $peminjaman->jumlah);

        $peminjaman->update([
            'status' => 'disetujui',
            'diproses_oleh' => $request->user()->id,
        ]);

        return response()->json($peminjaman->load(['barang', 'user']));
    }

    // Admin: tolak pengajuan
    public function reject(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->status !== 'pending') {
            return response()->json(['message' => 'Peminjaman ini sudah diproses.'], 422);
        }

        $peminjaman->update([
            'status' => 'ditolak',
            'diproses_oleh' => $request->user()->id,
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        return response()->json($peminjaman->load(['barang', 'user']));
    }

    // Admin: setujui perpanjangan
    public function approvePerpanjangan(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->status_perpanjangan !== 'pending') {
            return response()->json(['message' => 'Tidak ada permintaan perpanjangan yang menunggu.'], 422);
        }

        $peminjaman->update([
            'tanggal_kembali_rencana' => $peminjaman->tanggal_kembali_diminta,
            'status_perpanjangan' => 'disetujui',
        ]);

        return response()->json($peminjaman->load(['barang', 'user']));
    }

    // Admin: tolak perpanjangan
    public function rejectPerpanjangan(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->status_perpanjangan !== 'pending') {
            return response()->json(['message' => 'Tidak ada permintaan perpanjangan yang menunggu.'], 422);
        }

        $peminjaman->update([
            'status_perpanjangan' => 'ditolak',
        ]);

        return response()->json($peminjaman->load(['barang', 'user']));
    }

    // Siswa: tandai barang sudah dikembalikan
    public function kembalikan(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($peminjaman->status !== 'disetujui') {
            return response()->json(['message' => 'Peminjaman ini belum bisa dikembalikan.'], 422);
        }

        $validated = $request->validate([
            'catatan_pengembalian' => ['nullable', 'string', 'max:500'],
        ]);

        $peminjaman->update([
            'status' => 'dikembalikan',
            'tanggal_kembali_aktual' => now(),
            'catatan_pengembalian' => $validated['catatan_pengembalian'] ?? null,
        ]);

        return response()->json($peminjaman->load('barang'));
    }

    // Admin: verifikasi pengembalian, cek kondisi barang, hitung denda & biaya ganti
    public function verifikasi(Peminjaman $peminjaman, Request $request)
    {
        if ($peminjaman->status !== 'dikembalikan') {
            return response()->json(['message' => 'Peminjaman ini belum dikembalikan siswa.'], 422);
        }

        $validated = $request->validate([
            'kondisi_pengembalian' => ['required', 'in:baik,rusak_ringan,rusak_berat,hilang'],
        ]);

        $tarifDendaPerHari = 5000;
        $denda = 0;

        $tanggalRencana = \Carbon\Carbon::parse($peminjaman->tanggal_kembali_rencana);
        $tanggalAktual = \Carbon\Carbon::parse($peminjaman->tanggal_kembali_aktual);

        if ($tanggalAktual->greaterThan($tanggalRencana)) {
            $hariTerlambat = $tanggalRencana->diffInDays($tanggalAktual);
            $denda = $hariTerlambat * $tarifDendaPerHari;
        }

        $biayaGanti = 0;

        if ($validated['kondisi_pengembalian'] === 'hilang') {
            $biayaGanti = $peminjaman->barang->harga * $peminjaman->jumlah;
        } else {
            $peminjaman->barang->increment('stok', $peminjaman->jumlah);

            if (in_array($validated['kondisi_pengembalian'], ['rusak_ringan', 'rusak_berat'])) {
                $peminjaman->barang->update(['kondisi' => $validated['kondisi_pengembalian']]);
            }
        }

        $peminjaman->update([
            'status' => 'selesai',
            'diproses_oleh' => $request->user()->id,
            'denda' => $denda,
            'kondisi_pengembalian' => $validated['kondisi_pengembalian'],
            'biaya_ganti' => $biayaGanti,
        ]);

        return response()->json($peminjaman->load(['barang', 'user']));
    }
}
