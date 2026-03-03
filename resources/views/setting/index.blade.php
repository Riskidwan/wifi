@extends('layouts.layouts', ['menu' => 'setting', 'submenu' => ''])

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="main-panel">
    <div class "content">
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                    <div>
                        <h2 class="text-white pb-2 fw-bold">Konfigurasi Sistem</h2>
                        <h5 class="text-white op-7 mb-2">Kelola koneksi MikroTik dan pengaturan billing</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if(session('warning'))
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                                </div>
                            @endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                            <form action="{{ route('setting.update') }}" method="POST">
                                @csrf

                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs" id="settingTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="mikrotik-tab" data-toggle="tab" href="#mikrotik" role="tab">
                                            <i class="fas fa-server"></i> Koneksi MikroTik
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="billing-tab" data-toggle="tab" href="#billing" role="tab">
                                            <i class="fas fa-file-invoice"></i> Konfigurasi Billing
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="whatsapp-tab" data-toggle="tab" href="#whatsapp" role="tab">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content mt-4" id="settingTabContent">
                                    <!-- Tab MikroTik (TIDAK DIUBAH) -->
                                    <div class="tab-pane fade show active" id="mikrotik" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>IP Address MikroTik <span class="text-danger">*</span></label>
                                                    <input type="text" name="ip" class="form-control" 
                                                           value="{{ old('ip', $mikrotik['ip']) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Username <span class="text-danger">*</span></label>
                                                    <input type="text" name="user" class="form-control" 
                                                           value="{{ old('user', $mikrotik['user']) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Password</label>
                                                    <input type="password" name="password" class="form-control" 
                                                           value="{{ old('password', $mikrotik['password']) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab Billing (BARU) -->
                                    <div class="tab-pane fade" id="billing" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Nama Perusahaan <span class="text-danger">*</span></label>
                                                    <input type="text" name="company_name" class="form-control" 
                                                           value="{{ old('company_name', $billing->company_name) }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Alamat Perusahaan</label>
                                                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $billing->company_address) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Telepon Perusahaan</label>
                                                    <input type="text" name="company_phone" class="form-control" 
                                                           value="{{ old('company_phone', $billing->company_phone) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Email Perusahaan</label>
                                                    <input type="email" name="company_email" class="form-control" 
                                                           value="{{ old('company_email', $billing->company_email) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Tanggal Mulai Periode Tagihan <span class="text-danger">*</span></label>
                                                    <input type="number" name="billing_start_day" class="form-control" 
                                                           value="{{ old('billing_start_day', $billing->billing_start_day) }}" 
                                                           min="1" max="31" required>
                                                    <small class="form-text text-muted">Contoh: 1 (untuk tanggal 1 setiap bulan)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-group-default">
                                                    <label>Hari Jatuh Tempo Setelah Periode <span class="text-danger">*</span></label>
                                                    <input type="number" name="due_days_after_period" class="form-control" 
                                                           value="{{ old('due_days_after_period', $billing->due_days_after_period) }}" 
                                                           min="1" max="30" required>
                                                    <small class="form-text text-muted">Contoh: 5 (jatuh tempo 5 hari setelah periode berakhir)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ===== TAB WHATSAPP ===== --}}
                                    <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                                        <div class="mt-2">
                                            {{-- Token Input --}}
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label><strong>Fonnte API Token</strong></label>
                                                        <div class="input-group">
                                                            <input type="text" id="wa-token" class="form-control" 
                                                                   value="{{ $billing->wa_token ?? '' }}" 
                                                                   placeholder="Masukkan token dari fonnte.com">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" id="btn-save-token" type="button">
                                                                    <i class="fas fa-save"></i> Simpan Token
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Daftar di <a href="https://fonnte.com" target="_blank">fonnte.com</a> untuk mendapatkan API Token.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label><strong>Status Koneksi</strong></label>
                                                        <div id="wa-status-badge">
                                                            <span class="badge badge-secondary p-2">
                                                                <i class="fas fa-circle"></i> Mengecek...
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            {{-- QR Code & Actions --}}
                                            <div class="row">
                                                <div class="col-md-6 text-center">
                                                    <div id="wa-qr-area" style="min-height: 250px; display:flex; align-items:center; justify-content:center; border:2px dashed #ddd; border-radius:12px; padding:20px;">
                                                        <div id="wa-qr-content">
                                                            <p class="text-muted">Klik "Hubungkan WhatsApp" untuk menampilkan QR Code</p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button class="btn btn-success" id="btn-connect-wa" type="button">
                                                            <i class="fab fa-whatsapp"></i> Hubungkan WhatsApp
                                                        </button>
                                                        <button class="btn btn-danger d-none" id="btn-disconnect-wa" type="button">
                                                            <i class="fas fa-unlink"></i> Disconnect
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <h5><i class="fas fa-paper-plane"></i> Kirim Pesan Test</h5>
                                                            <p class="text-muted small">Pastikan WhatsApp sudah terhubung sebelum mengirim test.</p>
                                                            <div class="form-group">
                                                                <label>Nomor HP Tujuan</label>
                                                                <input type="text" id="wa-test-phone" class="form-control" placeholder="08xxxxxxxxxx">
                                                            </div>
                                                            <button class="btn btn-info btn-block" id="btn-test-wa" type="button">
                                                                <i class="fas fa-paper-plane"></i> Kirim Test
                                                            </button>
                                                            <div id="wa-test-result" class="mt-2"></div>
                                                        </div>
                                                    </div>

                                                    <div class="card mt-3">
                                                        <div class="card-body">
                                                            <h6><i class="fas fa-info-circle"></i> Panduan</h6>
                                                            <ol class="small mb-0 pl-3">
                                                                <li>Daftar di <a href="https://fonnte.com" target="_blank">fonnte.com</a></li>
                                                                <li>Salin API Token dari dashboard Fonnte</li>
                                                                <li>Paste token di kolom di atas → klik Simpan</li>
                                                                <li>Klik "Hubungkan WhatsApp" → scan QR</li>
                                                                <li>Setelah Connected, WA siap digunakan!</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>{{-- /.tab-content --}}

                                <div class="mt-4 text-right" id="btn-save-config">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Semua Konfigurasi
                                    </button>
                                </div>
                            </form>

                            <hr class="mt-5">
                            <h4 class="text-danger"><i class="fas fa-tools"></i> Alat Server (Auto-Fix)</h4>
                            <p class="text-muted small">Alat ini berguna saat Anda baru saja meng-upload source code ke server/hosting baru dengan database dan pelanggan lama.</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="card bg-light border-danger">
                                        <div class="card-body text-center">
                                            <h5>Sinkronisasi Data Pelanggan</h5>
                                            <p class="small text-muted mb-3">Klik tombol ini jika Anda tidak bisa generate tagihan karena data pelanggan lama belum memiliki Paket Internet, atau jika terjadi error "Duplicate entry INV-000...".</p>
                                            <form action="{{ route('setting.fixDataLama') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Sistem akan merapikan otomatis Kode Pelanggan dan memasukkan semua pelanggan lama yang statusnya menggantung ke Paket Default. Lanjutkan?')">
                                                    <i class="fas fa-magic"></i> Perbaiki Pelanggan Lama
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="card bg-light border-danger">
                                        <div class="card-body text-center">
                                            <h5>Update Database (Migrate)</h5>
                                            <p class="small text-muted mb-3">Klik tombol ini jika halaman Konfigurasi Billing atau halaman Invoices putih blank/error karena tabel konfigurasi belum terbuat di server Anda.</p>
                                            <a href="{{ url('/migrate-db') }}" class="btn btn-danger btn-block" onclick="return confirm('Ini akan memperbarui skema Database Anda dengan tabel-tabel baru. Lanjutkan?')">
                                                <i class="fas fa-database"></i> Jalankan Migrate DB
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- WhatsApp AJAX Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    let qrInterval = null;

    // === Cek status saat tab WhatsApp dibuka ===
    document.getElementById('whatsapp-tab').addEventListener('shown.bs.tab', checkStatus);
    document.getElementById('whatsapp-tab').addEventListener('click', function() {
        // Fallback for Bootstrap 4
        setTimeout(checkStatus, 300);
    });

    // === Toggle tombol "Simpan Semua Konfigurasi" berdasarkan tab aktif ===
    document.querySelectorAll('#settingTab .nav-link').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var saveBtn = document.getElementById('btn-save-config');
            if (this.id === 'whatsapp-tab') {
                saveBtn.style.display = 'none';
            } else {
                saveBtn.style.display = 'block';
            }
        });
    });

    // === Simpan Token ===
    document.getElementById('btn-save-token').addEventListener('click', function() {
        const token = document.getElementById('wa-token').value;
        if (!token) { alert('Token tidak boleh kosong!'); return; }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('{{ route("whatsapp.save-token") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ wa_token: token })
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save"></i> Simpan Token';
            if (data.success) checkStatus();
        })
        .catch(() => {
            alert('Gagal menyimpan token.');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save"></i> Simpan Token';
        });
    });

    // === Hubungkan (Get QR) ===
    document.getElementById('btn-connect-wa').addEventListener('click', function() {
        getQR();
    });

    // === Disconnect ===
    document.getElementById('btn-disconnect-wa').addEventListener('click', function() {
        if (!confirm('Yakin ingin disconnect WhatsApp?')) return;

        this.disabled = true;
        fetch('{{ route("whatsapp.disconnect") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            this.disabled = false;
            checkStatus();
        });
    });

    // === Test Kirim ===
    document.getElementById('btn-test-wa').addEventListener('click', function() {
        const phone = document.getElementById('wa-test-phone').value;
        if (!phone) { alert('Masukkan nomor HP!'); return; }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        document.getElementById('wa-test-result').innerHTML = '';

        fetch('{{ route("whatsapp.test") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ phone: phone })
        })
        .then(r => r.json())
        .then(data => {
            const cls = data.success ? 'alert-success' : 'alert-danger';
            document.getElementById('wa-test-result').innerHTML = 
                '<div class="alert ' + cls + ' small p-2">' + data.message + '</div>';
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Test';
        })
        .catch(() => {
            document.getElementById('wa-test-result').innerHTML = 
                '<div class="alert alert-danger small p-2">Gagal mengirim pesan.</div>';
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Test';
        });
    });

    // === Functions ===
    function checkStatus() {
        const badge = document.getElementById('wa-status-badge');
        badge.innerHTML = '<span class="badge badge-secondary p-2"><i class="fas fa-spinner fa-spin"></i> Mengecek...</span>';

        fetch('{{ route("whatsapp.status") }}')
        .then(r => r.json())
        .then(data => {
            if (data.connected) {
                badge.innerHTML = '<span class="badge badge-success p-2"><i class="fas fa-check-circle"></i> Connected</span>';
                if (data.device) badge.innerHTML += '<br><small class="text-muted">' + data.device + '</small>';
                document.getElementById('btn-connect-wa').classList.add('d-none');
                document.getElementById('btn-disconnect-wa').classList.remove('d-none');
                document.getElementById('wa-qr-content').innerHTML = 
                    '<div class="text-success"><i class="fas fa-check-circle fa-3x"></i><p class="mt-2">WhatsApp Terhubung!</p></div>';
                stopQrPolling();
            } else {
                badge.innerHTML = '<span class="badge badge-danger p-2"><i class="fas fa-times-circle"></i> Disconnected</span>';
                document.getElementById('btn-connect-wa').classList.remove('d-none');
                document.getElementById('btn-disconnect-wa').classList.add('d-none');
            }
        })
        .catch(() => {
            badge.innerHTML = '<span class="badge badge-warning p-2"><i class="fas fa-exclamation-triangle"></i> Error</span>';
        });
    }

    function getQR() {
        const content = document.getElementById('wa-qr-content');
        content.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat QR Code...</p>';

        fetch('{{ route("whatsapp.qr") }}')
        .then(r => r.json())
        .then(data => {
            if (data.connected) {
                content.innerHTML = '<div class="text-success"><i class="fas fa-check-circle fa-3x"></i><p class="mt-2">WhatsApp sudah terhubung!</p></div>';
                checkStatus();
                return;
            }
            if (data.qr_url) {
                content.innerHTML = '<img src="' + data.qr_url + '" style="max-width:220px;" alt="QR Code"><p class="mt-2 text-muted small">Scan dengan WhatsApp di HP</p>';
                startQrPolling();
            } else {
                content.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">' + (data.message || 'Gagal memuat QR') + '</p></div>';
            }
        })
        .catch(() => {
            content.innerHTML = '<div class="text-danger">Gagal menghubungi server.</div>';
        });
    }

    function startQrPolling() {
        stopQrPolling();
        qrInterval = setInterval(function() {
            fetch('{{ route("whatsapp.status") }}')
            .then(r => r.json())
            .then(data => {
                if (data.connected) {
                    checkStatus();
                    stopQrPolling();
                }
            });
        }, 5000); // cek setiap 5 detik
    }

    function stopQrPolling() {
        if (qrInterval) {
            clearInterval(qrInterval);
            qrInterval = null;
        }
    }
});
</script>
@endsection