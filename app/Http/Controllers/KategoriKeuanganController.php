<?php

namespace App\Http\Controllers;

use App\Models\KategoriKeuangan;
use Illuminate\Http\Request;

class KategoriKeuanganController extends Controller
{
    // =============== PEMASUKAN ===============
    public function indexPemasukan()
    {
        $kategoris = KategoriKeuangan::where('tipe', 'pemasukan')->get();
        return view('master.kategori_pemasukan', compact('kategoris'))
            ->with('menu', 'master')
            ->with('submenu', 'pemasukan');
    }

    public function storePemasukan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_keuangan,nama,NULL,id,tipe,pemasukan',
        ]);

        KategoriKeuangan::create([
            'nama' => $request->nama,
            'tipe' => 'pemasukan'
        ]);

        return back()->with('success', 'Kategori pemasukan berhasil ditambahkan!');
    }

    public function updatePemasukan(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_keuangan,nama,' . $id . ',id,tipe,pemasukan',
        ]);

        $kategori = KategoriKeuangan::where('tipe', 'pemasukan')->findOrFail($id);
        $kategori->update(['nama' => $request->nama]);

        return back()->with('success', 'Kategori pemasukan berhasil diperbarui!');
    }

    public function destroyPemasukan($id)
    {
        $kategori = KategoriKeuangan::where('tipe', 'pemasukan')->findOrFail($id);
        $kategori->delete();
        return back()->with('success', 'Kategori pemasukan berhasil dihapus!');
    }

    // =============== PENGELUARAN ===============
    public function indexPengeluaran()
    {
        $kategoris = KategoriKeuangan::where('tipe', 'pengeluaran')->get();
        return view('master.kategori_pengeluaran', compact('kategoris'))
            ->with('menu', 'master')
            ->with('submenu', 'pengeluaran');
    }

    public function storePengeluaran(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_keuangan,nama,NULL,id,tipe,pengeluaran',
        ]);

        KategoriKeuangan::create([
            'nama' => $request->nama,
            'tipe' => 'pengeluaran'
        ]);

        return back()->with('success', 'Kategori pengeluaran berhasil ditambahkan!');
    }

    public function updatePengeluaran(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori_keuangan,nama,' . $id . ',id,tipe,pengeluaran',
        ]);

        $kategori = KategoriKeuangan::where('tipe', 'pengeluaran')->findOrFail($id);
        $kategori->update(['nama' => $request->nama]);

        return back()->with('success', 'Kategori pengeluaran berhasil diperbarui!');
    }

    public function destroyPengeluaran($id)
    {
        $kategori = KategoriKeuangan::where('tipe', 'pengeluaran')->findOrFail($id);
        $kategori->delete();
        return back()->with('success', 'Kategori pengeluaran berhasil dihapus!');
    }
}