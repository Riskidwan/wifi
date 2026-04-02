@extends('layouts.layouts', ['menu' => 'master', 'submenu' => 'pengeluaran'])

@section('title', 'Master Kategori Pengeluaran')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white fw-bold">Master Kategori Pengeluaran</h2>
            </div>
        </div>

        <div class="page-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Daftar Kategori Pengeluaran</h4>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </button>
                        </div>
                        <div class="card-body">
                            @if($kategoris->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada kategori pengeluaran</p>
                                </div>
                            @else
                                <ul class="list-group">
                                    @foreach($kategoris as $k)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">{{ $k->nama }}</span>
                                        <div>
                                            <button class="btn btn-warning btn-sm me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $k->id }}"
                                                    data-nama="{{ $k->nama }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('master.kategori.pengeluaran.destroy', $k->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Pengeluaran</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('master.kategori.pengeluaran.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori Pengeluaran</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="editNama" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('#editModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const id = button.data('id');
    const nama = button.data('nama');
    
    $('#editId').val(id);
    $('#editNama').val(nama);
    $('#editModal form').attr('action', `/master/kategori/pengeluaran/${id}`);
});
</script>
@endpush
@endsection