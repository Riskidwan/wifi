@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pemasukan'])

@section('title', 'Tambah Pemasukan Manual')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Tambah Pemasukan Manual</h4>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form action="{{ route('laporan.pemasukan') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Kategori Pemasukan <span class="text-danger">*</span></label>
                                    <input type="text" name="kategori" class="form-control" required placeholder="Contoh: Jual Router, Servis, Donasi, dll">
                                </div>
                                <div class="form-group">
                                    <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="jumlah" class="form-control" required min="0">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', date('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Pemasukan</button>
                                <a href="{{ route('laporan.pemasukan') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection