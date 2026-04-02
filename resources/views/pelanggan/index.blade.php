@extends('layouts.layouts', ['menu' => 'pelanggan', 'submenu' => ''])

@section('title', 'Daftar Pelanggan')

@section('content')

<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Daftar Pelanggan</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $pelanggans->count() }} Pelanggan</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPelangganModal">
                            <i class="fa fa-plus"></i> Tambah Pelanggan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" class="d-flex">
                        {{-- <input 
                            type="text" 
                            name="search" 
                            class="form-control" 
                            placeholder="Cari berdasarkan ID atau Nama..." 
                            value="{{ request('search') }}"
                        >
                        <button type="submit" class="btn btn-primary ml-2">
                            <i class="fas fa-search"></i>
                        </button> --}}
                        @if(request('search'))
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <!-- Modal Tambah Pelanggan -->
                        <div class="modal fade" id="addPelangganModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header no-bd">
                                        <h5 class="modal-title">
                                            <span class="fw-mediumbold">Tambah</span>
                                            <span class="fw-light">Pelanggan Baru</span>
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
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

                                        <form action="{{ route('pelanggan.store') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                     <div class="form-group">
                                                        <label>Kode Pelanggan</label>
                                                        <input type="text" id="previewKode" class="form-control" value="Memuat..." readonly>
                                                        <small class="form-text text-muted">Akan di-generate otomatis saat disimpan</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nama Pelanggan <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_pelanggan" class="form-control" required>
                                                    </div>

                                                    <!-- 🔑 Pilihan Metode PPPoE -->
                                                    <div class="form-group">
    <label class="mb-2">Akun PPPoE</label>
    <div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="pppoe_method" value="generate" id="methodGenerate" checked>
            <label class="form-check-label" for="methodGenerate">
                <strong>Belum</strong>
            </label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="pppoe_method" value="use_existing" id="methodExisting">
            <label class="form-check-label" for="methodExisting">
                <strong>Sudah</strong>
            </label>
        </div>
    </div>
</div>


                                                    <!-- 🔁 Input Manual (Generate Otomatis) -->
                                                    <div class="form-group" id="manualPppoeGroup">
                                                        <label class="mb-2">Username PPPoE</label>
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                            </div>
                                                            <input type="text" name="username_pppoe" class="form-control" placeholder="Biarkan kosong untuk auto-generate">
                                                        </div>

                                                        <label class="mb-2">Password PPPoE</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                            </div>
                                                            <input type="text" name="password_pppoe" class="form-control" placeholder="Biarkan kosong untuk auto-generate">
                                                        </div>
                                                    </div>

                                                    <!-- 🔁 Dropdown: Gunakan yang Sudah Ada -->
                                                    <div class="form-group" id="existingPppoeGroup" style="display:none;">
                                                        <label class="mb-2">Pilih Akun PPPoE yang Tersedia</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fas fa-user-lock"></i>
                                                                </span>
                                                            </div>
                                                            <select id="existing_pppoe_select" class="form-control" style="max-height: 200px;">
                                                                <option value="" data-password="">-- Pilih akun PPPoE --</option>
                                                                @foreach($pppoeSecrets as $secret)
                                                                    @if($secret['name'])
                                                                        <option value="{{ $secret['name'] }}" data-password="{{ $secret['password'] }}">
                                                                            {{ $secret['name'] }} / {{ $secret['password'] }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <!-- Hidden inputs untuk kirim ke controller -->
                                                        <input type="hidden" name="existing_pppoe_username" id="existing_pppoe_username">
                                                        <input type="hidden" name="existing_pppoe_password" id="existing_pppoe_password">
                                                        <small class="form-text text-muted mt-1">
                                                            Format: <code>username / password</code> — pilih salah satu akun yang sudah ada di MikroTik
                                                        </small>
                                                    </div>

                                                   
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Paket Internet</label>
                                                        <select name="id_paket" class="form-control">
                                                            <option value="">Pilih Paket</option>
                                                            @foreach($pakets as $p)
                                                                <option value="{{ $p->id }}">{{ $p->nama_paket }} - {{ $p->kecepatan }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>No HP</label>
                                                        <input type="text" name="no_hp" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>No. Rekening BRIVA</label>
                                                        <input type="text" name="norekening_briva" class="form-control" placeholder="Contoh: 0069-01-555666-56-3">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Alamat</label>
                                                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <!-- Google Maps -->
<div class="form-group">
    <label>Link Google Maps (Opsional)</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="fas fa-map-marker-alt"></i>
            </span>
        </div>
        <input type="url" 
               name="google_maps_url" 
               class="form-control" 
               placeholder="https://maps.app.goo.gl/..." 
               value="{{ old('google_maps_url') }}">
    </div>
    <small class="form-text text-muted">
        Salin link "Bagikan" dari Google Maps<br>
        Contoh: <code>https://maps.app.goo.gl/Abc123</code>
    </small>
</div>
                                                    <div class="form-group">
                                                        <label>Foto Dokumen (KTP, Rumah, dll)</label>
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="fotoInput" name="foto[]" multiple accept="image/jpeg,image/png,image/jpg">
                                                                <label class="custom-file-label" for="fotoInput">Pilih file gambar</label>
                                                            </div>
                                                        </div>
                                                        <small class="form-text text-muted">Maks. 2MB per file</small>
                                                        <div class="mt-2" id="fotoPreview"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer no-bd">
                                                <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Daftar Pelanggan -->
                       <div class="table-responsive">
    <table id="pelangganTable" class="display table table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Paket</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pelanggans as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->kode_pelanggan }}</td>
                <td>{{ $p->nama_pelanggan }}</td>
                <td><code>{{ $p->username_pppoe }}</code></td>
                <td>{{ $p->paket ? $p->paket->nama_paket : 'default' }}</td>
                <td>{{ $p->alamat ?? '–' }}</td>
                <td>{{ $p->no_hp ?? '–' }}</td>
                <td>
                    <span class="badge badge-{{ $p->status_akun == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($p->status_akun) }}
                    </span>
                </td>
                <td>
                    <div class="form-button-action">
<button class="btn btn-link btn-info btn-lg" 
        data-toggle="modal" 
        data-target="#detailModal" 
        onclick="tampilkanDetail({{ $p->id_pelanggan }})">
    <i class="fa fa-eye"></i>
</button>

                        <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-link btn-warning btn-lg" data-toggle="tooltip" data-original-title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>

                        <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link btn-danger btn-lg" data-toggle="tooltip" data-original-title="Hapus" onclick="return confirm('Yakin ingin menghapus pelanggan {{ $p->nama_pelanggan }}? Akun PPPoE di MikroTik juga akan dihapus!')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pelanggan -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL GAMBAR -->
<div class="modal fade" id="gambarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0">
                <img src="" id="gambarBesar" class="img-fluid" style="max-height: 90vh; width: auto; display: block; margin: 0 auto;">
            </div>
            <div class="modal-footer justify-content-center bg-dark border-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {

$('#pelangganTable').DataTable({
        "processing": true,
        "serverSide": false, // karena kita pakai pagination Laravel
        "pageLength": 10,
        "order": [[0, 'asc']], // urutkan berdasarkan No
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_",
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
            { "orderable": false, "targets": [8] } // disable sorting di kolom Aksi
        ]
    });

    // Toggle PPPoE method
    $('input[name="pppoe_method"]').change(function() {
        if ($(this).val() === 'use_existing') {
            $('#existingPppoeGroup').fadeIn();
            $('#manualPppoeGroup').hide();
        } else {
            $('#existingPppoeGroup').hide();
            $('#manualPppoeGroup').fadeIn();
            // Reset hidden inputs ketika kembali ke manual
            $('#existing_pppoe_username').val('');
            $('#existing_pppoe_password').val('');
        }
    });

    // Populate hidden inputs saat pilih PPPoE dari dropdown
    $('#existing_pppoe_select').change(function() {
        var selected = $(this).find('option:selected');
        $('#existing_pppoe_username').val(selected.val());
        $('#existing_pppoe_password').val(selected.data('password'));
    });

    // Custom file label
    $('#fotoInput').on('change', function() {
        const fileName = Array.from(this.files).map(f => f.name).join(', ');
        $(this).next('.custom-file-label').html(fileName || 'Pilih file gambar');
    });

    // Preview kode pelanggan
$('#addPelangganModal').on('shown.bs.modal', function () {
    $.get('{{ route("pelanggan.preview.kode") }}')
        .done(function(response) {
            if (response.kode) {
                $('#previewKode').val(response.kode); // ✅ HANYA ANGKA 4 DIGIT
            } else {
                $('#previewKode').val('0001'); // fallback jika tidak ada data
            }
        })
        .fail(function() {
            $('#previewKode').val('0001'); // fallback jika error
        });
});

    // Preview foto
    document.getElementById('fotoInput').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('fotoPreview');
        previewContainer.innerHTML = '';
        if (!e.target.files) return;
        for (let i = 0; i < e.target.files.length; i++) {
            const file = e.target.files[i];
            if (!file.type.match('image.*')) continue;
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.createElement('div');
                preview.className = 'd-inline-block mr-2 mb-2';
                preview.innerHTML = `
                    <img src="${event.target.result}" class="img-thumbnail" style="width:100px; height:100px; object-fit: cover;">
                `;
                previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });

    // AJAX detail
    window.tampilkanDetail = function(id) {
        $('#detailContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Memuat data...</p>
            </div>
        `);
        $.get(`/pelanggan/${id}/detail`)
            .done(function(data) {
                $('#detailContent').html(data);
            })
            .fail(function(xhr) {
                $('#detailContent').html(`
                    <div class="alert alert-danger">Gagal memuat: ${xhr.status} ${xhr.statusText}</div>
                `);
            });
    };

    // Buka gambar besar
    $(document).on('click', '.clickable-foto', function() {
        const url = $(this).data('url');
        $('#gambarBesar').attr('src', url);
        $('#gambarModal').modal('show');
    });
});
</script>
@endpush

@endsection