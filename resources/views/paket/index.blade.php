@extends('layouts.layouts', ['menu' => 'paket', 'submenu' => ''])

@section('title', 'Paket Internet')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Paket Internet</h2>
                        <h5 class="text-white op-7 mb-2">Total: {{ $pakets->count() }} Paket</h5>
                    </div>
                    <div class="ml-md-auto py-2 py-md-0">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPaketModal">
                            <i class="fa fa-plus"></i> Tambah Paket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Modal Tambah Paket -->
                        <div class="modal fade" id="addPaketModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header no-bd">
                                        <h5 class="modal-title">
                                            <span class="fw-mediumbold">Tambah</span>
                                            <span class="fw-light">Paket Internet</span>
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('paket.store') }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>Nama Paket <small class="text-muted">(tanpa spasi)</small></label>
                                                <input type="text" name="nama_paket" class="form-control" placeholder="contoh: paket-10mbps" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Kecepatan (Rate Limit)</label>
                                                <input type="text" name="kecepatan" class="form-control" placeholder="contoh: 10M/5M">
                                            </div>
                                            <div class="form-group">
    <label>Local Address (Gateway PPPoE)</label>
    <input type="text" name="local_address" class="form-control" value="192.168.2.1" placeholder="Contoh: 192.168.2.1">
    <small class="form-text text-muted">IP gateway untuk pelanggan di subnet ini</small>
</div>

<div class="form-group">
    <label>Remote Address (IP Pool)</label>
    <input type="text" name="remote_address" class="form-control" value="pppoe-pool" placeholder="Contoh: pppoe-pool">
    <small class="form-text text-muted">Nama IP Pool yang sudah dibuat di MikroTik</small>
</div>
                                            <div class="form-group">
                                                <label>Harga (Rp)</label>
                                                <input type="number" name="harga" class="form-control" required>
                                            </div>

                                            <!-- PPN: Ya / Tidak -->
<div class="form-group">
    <label>PPN</label><br>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="ppn_aktif" id="ppnYa" value="1">
        <label class="form-check-label" for="ppnYa">Ya</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="ppn_aktif" id="ppnTidak" value="0" checked>
        <label class="form-check-label" for="ppnTidak">Tidak</label>
    </div>
</div>

<!-- Input PPN (muncul hanya jika "Ya" dipilih) -->
<div class="form-group" id="ppnInput" style="display:none;">
    <label>PPN (%)</label>
    <input type="number" name="ppn_persen" class="form-control" value="11" min="0" max="100">
</div>

<!-- Diskon: Ya / Tidak -->
<div class="form-group">
    <label>Diskon</label><br>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="diskon_aktif" id="diskonYa" value="1">
        <label class="form-check-label" for="diskonYa">Ya</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="diskon_aktif" id="diskonTidak" value="0" checked>
        <label class="form-check-label" for="diskonTidak">Tidak</label>
    </div>
</div>

<!-- Input Diskon (muncul hanya jika "Ya" dipilih) -->
<div class="form-group" id="diskonInput" style="display:none;">
    <label>Diskon (%)</label>
    <input type="number" name="diskon_persen" class="form-control" value="0" min="0" max="100">
</div>

                                            <div class="form-group">
                                                <label>Keterangan</label>
                                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Paket 10 Mbps non-kuota"></textarea>
                                            </div>
                                            <div class="modal-footer no-bd">
                                                <button type="submit" class="btn btn-primary">Simpan Paket</button>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Daftar Paket -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Paket</th>
                                        <th>Kecepatan</th>
                                        <th>Harga (Rp)</th>
                                        <th>Diskon</th>
                                        <th>PPN</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pakets as $no => $p)
                                        <tr>
                                            <td>{{ $no + 1 }}</td>
                                            <td><code>{{ $p->nama_paket }}</code></td>
                                            <td>{{ $p->kecepatan ?: '-' }}</td>
                                            <td>{{ number_format($p->harga, 0, ',', '.') }}</td>
                                            <td>{{ $p->diskon_persen }}%</td>
                                            <!-- Kolom PPN -->
<td>
    @if($p->ppn_aktif)
        {{ $p->ppn_persen }}%
    @else
        Tidak
    @endif
</td>
                                            <td>{{ $p->keterangan ?: '-' }}</td>
                                            <td>
                                                <a href="{{ route('paket.edit', $p->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('paket.destroy', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus paket ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada paket internet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>@push('scripts')
<script>
$(document).ready(function() {
    $('#addPaketModal').on('shown.bs.modal', function () {
        // PPN
        const ppnYa = document.getElementById('ppnYa');
        const ppnTidak = document.getElementById('ppnTidak');
        const ppnInput = document.getElementById('ppnInput');

        if (ppnYa && ppnTidak && ppnInput) {
            function togglePPN() {
                ppnInput.style.display = ppnYa.checked ? 'block' : 'none';
            }
            ppnYa.addEventListener('change', togglePPN);
            ppnTidak.addEventListener('change', togglePPN);
            togglePPN();
        }

        // Diskon
        const diskonYa = document.getElementById('diskonYa');
        const diskonTidak = document.getElementById('diskonTidak');
        const diskonInput = document.getElementById('diskonInput');

        if (diskonYa && diskonTidak && diskonInput) {
            function toggleDiskon() {
                diskonInput.style.display = diskonYa.checked ? 'block' : 'none';
            }
            diskonYa.addEventListener('change', toggleDiskon);
            diskonTidak.addEventListener('change', toggleDiskon);
            toggleDiskon();
        }
    });
});
</script>
@endpush
@endsection