@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pemasukan'])

@section('title', 'Edit Pemasukan Manual')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white fw-bold">Edit Pemasukan Manual</h2>
            </div>
        </div>

        <div class="page-inner">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Form Edit Pemasukan</h4>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('pemasukan.manual.update', $pemasukan->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" 
                                           value="{{ old('tanggal', $pemasukan->tanggal) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Kategori <span class="text-danger">*</span></label>
                                    <input type="text" name="kategori" class="form-control" 
                                           value="{{ old('kategori', $pemasukan->kategori) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $pemasukan->keterangan) }}</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah" class="form-control" 
                                           value="{{ old('jumlah', $pemasukan->jumlah) }}" required step="0.01" min="0">
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="{{ route('pemasukan.manual.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
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