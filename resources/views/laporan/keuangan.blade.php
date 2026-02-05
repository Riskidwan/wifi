@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'keuangan'])

@section('title', 'Laporan Keuangan')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white fw-bold">Laporan Keuangan</h2>
            </div>
        </div>

        <div class="page-inner">
            <!-- Filter Tanggal -->
            <form method="GET" class="row mb-4">
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('laporan.keuangan.export') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    
                </div>
            </form>
          

            <!-- Ringkasan -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>Pendapatan</h5>
                            <h3>Rp {{ number_format($income, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5>Pengeluaran</h5>
                            <h3>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-{{ $netProfit >= 0 ? 'info' : 'dark' }} text-white">
                        <div class="card-body text-center">
                            <h5>Saldo Bersih</h5>
                            <h3>Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Pemasukan -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="d-flex justify-content-between align-items-center">
    <h4 class="card-title">Rincian Pemasukan</h4>
    <button class="btn btn-success" data-toggle="modal" data-target="#addIncomeModal">
        <i class="fas fa-plus"></i> Tambah Pemasukan
    </button>
</div>  
                        <div class="card-body">
                            <!-- Ganti bagian ini -->
@if($incomeList->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-receipt fa-3x text-muted mb-2"></i>
        <p class="text-muted">Belum ada pemasukan</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomeList as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}</td>
                        <td>
                            @if($item['type'] === 'payment')
                                {{ $item['customer'] }}
                            @else
                                <span class="badge badge-info">Manual</span>
                            @endif
                        </td>
                        <td>
                            @if($item['type'] === 'payment')
                                <code>{{ $item['invoice'] }}</code>
                            @else
                                {{ $item['source'] }}
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- ❌ HAPUS BARIS INI --}}
    {{-- {{ $incomeList->appends(request()->query())->links() }} --}}
@endif
                        </div>
                    </div>
                </div>

                <!-- Pengeluaran -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Rincian Pengeluaran</h4>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                                <i class="fas fa-plus"></i> Tambah Pengeluaran
                            </button>
                        </div>
                        <div class="card-body">
                            @if($expenseList->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-money-bill-alt fa-3x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada pengeluaran</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Deskripsi</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expenseList as $expense)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ 
                                                        $expense->category == 'bandwidth' ? 'primary' : 
                                                        ($expense->category == 'salary' ? 'success' : 
                                                        ($expense->category == 'equipment' ? 'warning' : 'secondary'))
                                                    }}">
                                                        {{ $expense->category_name }}
                                                    </span>
                                                </td>
                                                <td>{{ $expense->description }}</td>
                                                <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $expenseList->appends(request()->query())->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengeluaran</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('pengeluaran.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
<div class="form-group">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="bandwidth">Bandwidth</option>
                            <option value="equipment">Alat/Perangkat</option>
                            <option value="salary">Gaji</option>
                            <option value="maintenance">Perawatan</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required step="0.01">
                    </div>
                    
                    {{-- <div class="form-group">
                        <label>No. Bon</label>
                        <input type="text" name="receipt_number" class="form-control">
                    </div> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Pemasukan -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pemasukan Manual</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('pemasukan.manual.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="kategori" class="form-control" placeholder="Contoh: Jual Router, Servis, Donasi" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi semua input tanggal
    flatpickr(".flatpickr-date", {
        dateFormat: "d-m-Y",       // Tampilan: 28-01-2026
        altInput: true,            // Buat input tersembunyi untuk format Y-m-d
        altFormat: "Y-m-d",        // Format internal untuk database
        altInputClass: "form-control d-none", // Sembunyikan input internal
        allowInput: true,
        clickOpens: true,
        locale: {
            firstDayOfWeek: 1 // Senin sebagai hari pertama
        }
    });
});
$(document).ready(function() {
    // Auto-focus modal
    $('#addExpenseModal').on('shown.bs.modal', function() {
        $(this).find('input:first').focus();
    });
});
</script>
@endpush
@endsection