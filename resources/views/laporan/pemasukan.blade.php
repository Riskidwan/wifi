@extends('layouts.layouts', ['menu' => 'laporan', 'submenu' => 'pemasukan'])

@section('title', 'Pemasukan Manual')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Pemasukan Manual</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $pemasukan->total() }} Pemasukan</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <button class="btn btn-success" data-toggle="modal" data-target="#addIncomeModal">
                            <i class="fas fa-plus"></i> Tambah Pemasukan
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
                            @if($pemasukan->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-receipt fa-3x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada pemasukan manual</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table id="pemasukanTable" class="display table table-striped table-hover" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Keterangan</th>
                                                <th class="text-right">Jumlah</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pemasukan as $item)
                                            <tr>
                                                <td>{{ $loop->iteration + ($pemasukan->currentPage() - 1) * $pemasukan->perPage() }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                                <td>{{ $item->kategori }}</td>
                                                <td>{{ $item->keterangan ?? '-' }}</td>
                                                <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('pemasukan.manual.edit', $item->id) }}" 
                                                       class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pemasukan.manual.destroy', $item->id) }}" 
                                                          method="POST" style="display:inline;" 
                                                          onsubmit="return confirm('Hapus pemasukan ini?')">
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

                    <div class="form-group mb-3">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" 
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>

                   <div class="form-group mb-3">
    <label>Kategori <span class="text-danger">*</span></label>
    <select name="kategori" class="form-control" required>
        <option value="">-- Pilih Kategori --</option>
        @foreach($kategori_pemasukan as $k)
            <option value="{{ $k->nama }}" {{ old('kategori', $pemasukan->kategori ?? '') == $k->nama ? 'selected' : '' }}>
                {{ $k->nama }}
            </option>
        @endforeach
    </select>
</div>

                    <div class="form-group mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" 
                                  placeholder="Opsional">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" 
                               value="{{ old('jumlah') }}" step="0.01" min="0" required>
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
$(document).ready(function() {
    $('#pemasukanTable').DataTable({
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