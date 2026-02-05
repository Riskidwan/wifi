@extends('layouts.layouts', ['menu' => 'pelanggan', 'submenu' => ''])

@section('title', 'Edit Pelanggan')

@section('content')

<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Edit Pelanggan</h2>
                        <h5 class="text-white op-7 mb-2">Mengedit: {{ $pelanggan->nama_pelanggan }}</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-light">
                            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Form Edit Data Pelanggan</div>
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

                            <form action="{{ route('pelanggan.update', $pelanggan->id_pelanggan) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Kode Pelanggan</label>
                                            <input type="text" class="form-control" value="{{ $pelanggan->kode_pelanggan }}" readonly>
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>Nama Pelanggan <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}" required>
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>Username PPPoE</label>
                                            <input type="text" name="username_pppoe" class="form-control" value="{{ old('username_pppoe', $pelanggan->username_pppoe) }}">
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>Password PPPoE</label>
                                            <input type="text" name="password_pppoe" class="form-control" value="{{ old('password_pppoe', $pelanggan->password_pppoe) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group form-group-default">
                                            <label>Paket Internet</label>
                                            <select name="id_paket" class="form-control">
                                                <option value="">Pilih Paket</option>
                                                @foreach($pakets as $p)
                                                    <option value="{{ $p->id }}" {{ (old('id_paket', $pelanggan->id_paket) == $p->id) ? 'selected' : '' }}>
                                                        {{ $p->nama_paket }} - {{ $p->kecepatan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $pelanggan->email) }}">
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>No HP</label>
                                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $pelanggan->no_hp) }}">
                                        </div>
                                        <div class="form-group form-group-default">
                                            <label>Alamat</label>
                                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                                        </div>
                                         <div class="form-group form-group-default">
        <label>Link Google Maps (Opsional)</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-map-marker-alt"></i>
                </span>
            </div>
            <input 
                type="url" 
                name="google_maps_url" 
                class="form-control" 
                value="{{ old('google_maps_url', $pelanggan->google_maps_url) }}"
                placeholder="https://maps.app.goo.gl/...">
        </div>
        <small class="form-text text-muted">
            Salin link "Bagikan" dari Google Maps<br>
            Contoh: <code>https://maps.app.goo.gl/Abc123</code>
        </small>
    </div>
                                        <div class="form-group form-group-default">
                                            <label>Status Akun</label>
                                            <select name="status_akun" class="form-control" required>
                                                <option value="active" {{ old('status_akun', $pelanggan->status_akun) == 'active' ? 'selected' : '' }}>Aktif</option>
                                                <option value="inactive" {{ old('status_akun', $pelanggan->status_akun) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Foto Dokumen Saat Ini</label>
                                            @if($pelanggan->foto && is_array($pelanggan->foto))
                                                <div class="row">
                                                    @foreach($pelanggan->foto as $foto)
                                                        <div class="col-6 col-md-3 mb-3">
                                                            <img src="{{ asset('storage/' . $foto) }}"
                                                                 class="img-fluid border rounded"
                                                                 style="max-height: 120px; object-fit: cover;"
                                                                 onerror="this.onerror=null; this.src='{{ asset('template/img/default-image.png') }}'">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">Tidak ada foto tersimpan.</p>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label>Tambah Foto Baru</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="foto[]" multiple accept="image/jpeg,image/png,image/jpg">
                                                <label class="custom-file-label">Pilih file gambar (opsional)</label>
                                            </div>
                                            <small class="form-text text-muted">File baru akan ditambahkan ke daftar. Max 2MB per file.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 text-right">
                                    <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Update label custom file
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).siblings('.custom-file-label').addClass("selected").html(fileName || "Pilih file gambar");
});
</script>
@endpush

@endsection