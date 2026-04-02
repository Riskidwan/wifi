@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pengeluaran'])

@section('title', 'Edit Pengeluaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white fw-bold">Edit Pengeluaran</h2>
            </div>
        </div>

        <div class="page-inner">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Form Edit Pengeluaran</h4>
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

                            <form action="{{ route('pengeluaran.update', $expense->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label>Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date" class="form-control" 
                                           value="{{ old('expense_date', $expense->expense_date) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Kategori <span class="text-danger">*</span></label>
                                    <select name="category" class="form-control" required>
                                        <option value="bandwidth" {{ $expense->category == 'bandwidth' ? 'selected' : '' }}>Bandwidth</option>
                                        <option value="equipment" {{ $expense->category == 'equipment' ? 'selected' : '' }}>Alat/Perangkat</option>
                                        <option value="salary" {{ $expense->category == 'salary' ? 'selected' : '' }}>Gaji</option>
                                        <option value="maintenance" {{ $expense->category == 'maintenance' ? 'selected' : '' }}>Perawatan</option>
                                        <option value="other" {{ $expense->category == 'other' ? 'selected' : '' }}>Lain-lain</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Deskripsi <span class="text-danger">*</span></label>
                                    <input type="text" name="description" class="form-control" 
                                           value="{{ old('description', $expense->description) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" 
                                           value="{{ old('amount', $expense->amount) }}" required step="0.01" min="1">
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="{{ route('pengeluaran.index') }}" class="btn btn-secondary">
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