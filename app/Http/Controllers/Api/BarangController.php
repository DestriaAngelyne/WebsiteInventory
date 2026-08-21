<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        return response()->json(Barang::latest()->get());
    }

    public function tersedia()
    {
        return response()->json(Barang::where('stok', '>', 0)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'harga' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        }

        $barang = Barang::create($validated);

        return response()->json($barang, 201);
    }

    public function show(Barang $barang)
    {
        return response()->json($barang);
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'harga' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        }

        $barang->update($validated);

        return response()->json($barang);
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return response()->json(['message' => 'Barang berhasil dihapus.']);
    }
}
