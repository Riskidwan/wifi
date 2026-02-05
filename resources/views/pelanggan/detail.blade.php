<!-- resources/views/pelanggan/detail.blade.php -->

<div class="row">
    <div class="col-md-6">
        <h5>Data Pelanggan</h5>
        <table class="table table-borderless">
            <tr>
                <td><strong>Kode</strong></td>
                <td>{{ $pelanggan->kode_pelanggan }}</td>
            </tr>
            <tr>
                <td><strong>Nama</strong></td>
                <td>{{ $pelanggan->nama_pelanggan }}</td>
            </tr>
            <tr>
                <td><strong>Username PPPoE</strong></td>
                <td><code>{{ $pelanggan->username_pppoe }}</code></td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td>{{ $pelanggan->email }}</td>
            </tr>
            <tr>
                <td><strong>No HP</strong></td>
                <td>{{ $pelanggan->no_hp }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>
                    <span class="badge badge-{{ $pelanggan->status_akun == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($pelanggan->status_akun) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h5>Detail Lokasi</h5>
        <table class="table table-borderless">
            <tr>
                <td><strong>Alamat</strong></td>
                <td>{{ $pelanggan->alamat ?? 'Tidak diisi' }}</td>
            </tr>
            <tr>
                <td><strong>Link Google Maps</strong></td>
                <td>
                    @if($pelanggan->google_maps_url)
                        <a href="{{ $pelanggan->google_maps_url }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-map-marker-alt"></i> Buka di Google Maps
                        </a>
                    @else
                        <span class="text-muted">Belum diisi</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Jika ingin tampilkan foto juga -->
        @if($pelanggan->foto && count($pelanggan->foto) > 0)
            <h5>Foto Dokumen</h5>
            <div class="d-flex flex-wrap">
                @foreach($pelanggan->foto as $foto)
                    <div class="m-1">
                        <img src="{{ asset('storage/' . $foto) }}" 
                             class="img-thumbnail clickable-foto" 
                             data-url="{{ asset('storage/' . $foto) }}"
                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                             alt="Foto">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>