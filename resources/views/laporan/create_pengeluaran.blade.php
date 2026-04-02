@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pengeluaran'])

@section('title', 'Tambah Pengeluaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Tambah Pengeluaran</h2>
                        <h5 class="text-white op-7 mb-2">Catat biaya operasional, belanja, gaji, dll</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Form Input Pengeluaran</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('pengeluaran.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori" class="form-control" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="gaji">Gaji</option>
                                        <option value="belanja">Belanja</option>
                                        <option value="alat">Pembelian Alat</option>
                                        <option value="internet">Biaya Internet</option>
                                        <option value="perawatan">Perawatan</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" class="form-control" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional"></textarea>
                                </div>
                                <div class="mt-3 text-right">
                                    <a href="{{ route('laporan.pengeluaran') }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection