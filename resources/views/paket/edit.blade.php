@extends('layouts.layouts', ['menu' => 'paket', 'submenu' => ''])

@section('title', 'Edit Paket Internet')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <h2 class="text-white pb-2 fw-bold">Edit Paket Internet</h2>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('paket.update', $paket->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>Nama Paket</label>
                                <input type="text" name="nama_paket" class="form-control" value="{{ old('nama_paket', $paket->nama_paket) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Kecepatan (Rate Limit)</label>
                                <input type="text" name="kecepatan" class="form-control" value="{{ old('kecepatan', $paket->kecepatan) }}">
                            </div>
                            <div class="form-group">
                                <label>Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="{{ old('harga', $paket->harga) }}" required>
                            </div>

                            <!-- PPN: Ya / Tidak -->
                            <div class="form-group">
                                <label>PPN</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppn_aktif" id="ppnYa" value="1" {{ old('ppn_aktif', $paket->ppn_aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ppnYa">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppn_aktif" id="ppnTidak" value="0" {{ old('ppn_aktif', $paket->ppn_aktif) ? '' : 'checked' }}>
                                    <label class="form-check-label" for="ppnTidak">Tidak</label>
                                </div>
                            </div>

                            <!-- Input PPN (muncul hanya jika "Ya" dipilih) -->
                            <div class="form-group" id="ppnInput" style="display:{{ old('ppn_aktif', $paket->ppn_aktif) ? 'block' : 'none' }};">
                                <label>PPN (%)</label>
                                <input type="number" name="ppn_persen" class="form-control" value="{{ old('ppn_persen', $paket->ppn_persen ?? 11) }}" min="0" max="100">
                            </div>

                            <!-- Diskon: Ya / Tidak -->
                            <div class="form-group">
                                <label>Diskon</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diskon_aktif" id="diskonYa" value="1" {{ old('diskon_aktif', $paket->diskon_aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="diskonYa">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="diskon_aktif" id="diskonTidak" value="0" {{ old('diskon_aktif', $paket->diskon_aktif) ? '' : 'checked' }}>
                                    <label class="form-check-label" for="diskonTidak">Tidak</label>
                                </div>
                            </div>

                            <!-- Input Diskon (muncul hanya jika "Ya" dipilih) -->
                            <div class="form-group" id="diskonInput" style="display:{{ old('diskon_aktif', $paket->diskon_aktif) ? 'block' : 'none' }};">
                                <label>Diskon (%)</label>
                                <input type="number" name="diskon_persen" class="form-control" value="{{ old('diskon_persen', $paket->diskon_persen ?? 0) }}" min="0" max="100">
                            </div>

                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $paket->keterangan) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Paket</button>
                            <a href="{{ route('paket.index') }}" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // PPN Toggle
    const ppnYa = document.getElementById('ppnYa');
    const ppnTidak = document.getElementById('ppnTidak');
    const ppnInput = document.getElementById('ppnInput');

    function togglePPN() {
        ppnInput.style.display = ppnYa.checked ? 'block' : 'none';
    }

    if (ppnYa && ppnTidak) {
        ppnYa.addEventListener('change', togglePPN);
        ppnTidak.addEventListener('change', togglePPN);
    }

    // Diskon Toggle
    const diskonYa = document.getElementById('diskonYa');
    const diskonTidak = document.getElementById('diskonTidak');
    const diskonInput = document.getElementById('diskonInput');

    function toggleDiskon() {
        diskonInput.style.display = diskonYa.checked ? 'block' : 'none';
    }

    if (diskonYa && diskonTidak) {
        diskonYa.addEventListener('change', toggleDiskon);
        diskonTidak.addEventListener('change', toggleDiskon);
    }
});
</script>
@endpush
@endsection