@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pengeluaran'])

@section('title', 'Pengeluaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Pengeluaran</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $expenses->total() }} Pengeluaran</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                            <i class="fas fa-plus"></i> Tambah Pengeluaran
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if($expenses->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-money-bill-alt fa-3x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada pengeluaran</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table id="pengeluaranTable" class="display table table-striped table-hover" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Deskripsi</th>
                                                <th class="text-right">Jumlah</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expenses as $expense)
                                            <tr>
                                                <td>{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
                                                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ 
                                                        $expense->category == 'bandwidth' ? 'primary' : 
                                                        ($expense->category == 'salary' ? 'success' : 
                                                        ($expense->category == 'equipment' ? 'warning' : 'secondary'))
                                                    }}">
                                                        {{ ucfirst($expense->category) }}
                                                    </span>
                                                </td>
                                                <td>{{ $expense->description }}</td>
                                                <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('pengeluaran.edit', $expense->id) }}" 
                                                       class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pengeluaran.destroy', $expense->id) }}" 
                                                          method="POST" style="display:inline;" 
                                                          onsubmit="return confirm('Hapus pengeluaran ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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

                    <div class="form-group mb-3">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" 
                               value="{{ old('expense_date', now()->format('Y-m-d')) }}" required>
                    </div>

                   <div class="form-group mb-3">
    <label>Kategori <span class="text-danger">*</span></label>
    <select name="category" class="form-control" required>
        <option value="">-- Pilih Kategori --</option>
        @if(isset($kategori_pengeluaran))
            @foreach($kategori_pengeluaran as $k)
                <option value="{{ $k->nama }}" {{ old('category') == $k->nama ? 'selected' : '' }}>
                    {{ $k->nama }}
                </option>
            @endforeach
        @endif
    </select>
</div>

                    <div class="form-group mb-3">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" 
                               value="{{ old('description') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" 
                               value="{{ old('amount') }}" step="0.01" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#pengeluaranTable').DataTable({
        "processing": true,
        "serverSide": false,
        "pageLength": {{ request('per_page', 10) }},
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
        "order": [[0, 'asc']],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total entri)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Berikutnya",
                "previous": "Sebelumnya"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        "columnDefs": [
            { "orderable": false, "targets": [5] }
        ]
    });
});
</script>
@endpush
@endsection