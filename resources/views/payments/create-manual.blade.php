@extends('layouts.layouts', ['menu' => 'pembayaran', 'submenu' => 'manual'])

@section('title', 'Pembayaran Manual - Bayar di Muka')

@section('content')
<div class="main-panel">
    <div class="content">
        <div class="page-inner">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pembayaran Manual - Bayar di Muka</h4>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
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
                            

                            <form action="{{ route('payments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="is_manual" value="1">

                                <!-- Info Tagihan -->
                                <div id="invoiceInfo" class="alert alert-info mt-3" style="display:none;">
                                    <h6><i class="fas fa-info-circle"></i> Informasi Tagihan</h6>
                                    <div id="invoiceList"></div>
                                </div>

                                <!-- Pelanggan -->
                                <div class="form-group">
                                    <label>Pelanggan <span class="text-danger">*</span></label>
                                    <select name="pelanggan_id" class="form-control" required>
                                        <option value="">-- Pilih Pelanggan --</option>
                                        @foreach($pelanggans as $p)
                                            <option value="{{ $p->id_pelanggan }}">
                                                {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Jumlah Bulan -->
                                <div class="form-group">
                                    <label>Jumlah Bulan <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah_bulan" class="form-control" min="1" max="12" value="1" required>
                                    <small class="form-text text-muted">Maksimal 12 bulan sekaligus</small>
                                </div>

                                <!-- Total Bayar -->
                                <div class="form-group">
                                    <label>Total Bayar</label>
                                    <input type="text" id="totalBayar" class="form-control" readonly>
                                <input type="hidden" name="amount_paid" value="0" required>
                                </div>

                                <!-- Uang Dibayar -->
                                <div class="form-group">
                                    <label>Uang Dibayar <span class="text-danger">*</span></label>
                                    <input type="text" id="uangDibayar" class="form-control" placeholder="Contoh: 200.000">
                                    <input type="hidden" name="uang_dibayar_raw" id="uangDibayarRaw">
                                </div>

                                <!-- Kembalian -->
                                <div class="form-group">
                                    <label>Kembalian</label>
                                    <input type="text" id="kembalian" class="form-control" readonly>
                                    <input type="hidden" name="kembalian_raw" id="kembalianRaw">
                                </div>

                                <!-- Tanggal Bayar -->
                                <div class="form-group">
                                    <label>Tanggal Bayar <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div class="form-group">
                                    <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="cash">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="e-wallet">E-Wallet</option>
                                    </select>
                                </div>

                                <!-- Catatan -->
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>

                                <!-- Nomor Struk -->
                                <div class="form-group">
                                    <label>Nomor Struk <span class="text-secondary">(Otomatis)</span></label>
                                    <input type="text" name="receipt_number" class="form-control" 
                                           value="Otomatis mengikuti ID Pelanggan" 
                                           readonly>
                                </div>

                                <!-- Nama Kasir -->
                                <div class="form-group">
                                    <label>Nama Kasir</label>
                                    <input type="text" name="cashier_name" class="form-control" 
                                           value="{{ auth()->user()->name ?? 'admin' }}" 
                                           readonly>
                                </div>

                                <!-- Submit -->
                                <button type="submit" class="btn btn-primary">Simpan & Cetak Struk</button>
                                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Batal</a>
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
$(document).ready(function() {
    function cleanNumber(value) {
        return (value || '').replace(/\D/g, '');
    }

    $('form').on('submit', function(e) {
        let total = parseFloat($('input[name="amount_paid"]').val()) || 0;
        let uang = parseFloat(cleanNumber($('#uangDibayar').val())) || 0;
        if (uang < total) {
            e.preventDefault();
            alert('Uang yang dibayar kurang dari total tagihan!');
            $('#uangDibayar').focus();
        }
    });

    $('#uangDibayar').on('input', function() {
        let raw = cleanNumber(this.value);
        this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        hitungSemua();
    });

    function hitungSemua() {
    let pelangganId = $('select[name="pelanggan_id"]').val();
    let jumlahBulan = parseInt($('input[name="jumlah_bulan"]').val()) || 1;
    let uangRaw = cleanNumber($('#uangDibayar').val());
    let uangDibayar = parseFloat(uangRaw) || 0;

    if (!pelangganId) {
        $('#totalBayar').val('Rp 0');
        $('input[name="amount_paid"]').val('0');
        $('#kembalian').val('Rp 0');
        $('#uangDibayarRaw').val('');
        $('#kembalianRaw').val('');
        return;
    }

    // Ambil data tagihan dari API
    $.get(`/api/pelanggan/${pelangganId}/unpaid-invoices`)
        .done(function(data) {
            if (data.invoices && data.invoices.length > 0) {
                // Ambil hanya N tagihan pertama (sesuai jumlah_bulan)
                let invoicesToPay = data.invoices.slice(0, jumlahBulan);
                let total = invoicesToPay.reduce((sum, inv) => sum + inv.total, 0);

                $('#totalBayar').val('Rp ' + Math.round(total).toLocaleString('id-ID'));
                $('input[name="amount_paid"]').val(Math.round(total));

                let kembalian = uangDibayar - total;
                $('#kembalian').val('Rp ' + (kembalian >= 0 ? kembalian : 0).toLocaleString('id-ID'));
                $('#uangDibayarRaw').val(uangRaw);
                $('#kembalianRaw').val(kembalian >= 0 ? kembalian : 0);
            } else {
                // Jika tidak ada tagihan, gunakan harga paket (opsional)
                $.get(`/api/pelanggan/${pelangganId}/paket`)
                    .done(function(paketData) {
                        let harga = parseFloat(paketData.harga) || 0;
                        let ppnPersen = paketData.ppn_aktif ? (parseFloat(paketData.ppn_persen) || 0) : 0;
                        let diskonPersen = paketData.diskon_aktif ? (parseFloat(paketData.diskon_persen) || 0) : 0;
                        
                        let ppn = harga * (ppnPersen / 100);
                        let diskon = harga * (diskonPersen / 100);
                        let totalBulanan = harga + ppn - diskon;
                        let total = totalBulanan * jumlahBulan;
                        
                        $('#totalBayar').val('Rp ' + Math.round(total).toLocaleString('id-ID'));
                        $('input[name="amount_paid"]').val(Math.round(total));
                    });
            }
        })
        .fail(function() {
            $('#totalBayar').val('Rp 0');
            $('input[name="amount_paid"]').val('0');
            $('#kembalian').val('Rp 0');
            $('#uangDibayarRaw').val('');
            $('#kembalianRaw').val('');
        });
}

    function loadInvoiceInfo(pelangganId) {
    if (!pelangganId) {
        $('#invoiceInfo').hide();
        return;
    }

    $.get(`/api/pelanggan/${pelangganId}/unpaid-invoices`, function(data) {
        if (data.invoices && data.invoices.length > 0) {
            let html = '<div class="invoice-detail">';
            data.invoices.forEach(inv => {
                let dasar = parseInt(inv.amount_dasar).toLocaleString('id-ID');
                let ppn = parseInt(inv.ppn_value || 0).toLocaleString('id-ID');
                let diskon = parseInt(inv.diskon_value || 0).toLocaleString('id-ID');
                let total = parseInt(inv.total).toLocaleString('id-ID');

                html += `
                    <div class="mb-3">
                        <strong>${inv.periode}</strong><br>
                        <small>
                            • Paket Internet: Rp ${dasar}<br>
                            • PPN ${inv.ppn_persen}%: Rp ${ppn}<br>
                            • Diskon ${inv.diskon_persen}%: -Rp ${diskon}<br>
                            → <strong>Total: Rp ${total}</strong>
                        </small>
                    </div>
                `;
            });
            html += '</div>';
            $('#invoiceList').html(html);
            $('#invoiceInfo').removeClass('alert-warning alert-danger').addClass('alert-info').show();
        } else {
            $('#invoiceList').html('<p class="mb-0">Tidak ada tagihan belum bayar.</p>');
            $('#invoiceInfo').removeClass('alert-info alert-danger').addClass('alert-warning').show();
        }
    }).fail(function() {
        $('#invoiceList').html('<p class="mb-0 text-danger">Gagal memuat informasi tagihan.</p>');
        $('#invoiceInfo').removeClass('alert-info alert-warning').addClass('alert-danger').show();
    });
}

    const nextReceipts = @json($nextReceipts);

    $('select[name="pelanggan_id"]').on('change', function() {
        let pelangganId = $(this).val();
        loadInvoiceInfo(pelangganId);
        hitungSemua();

        if (pelangganId && nextReceipts[pelangganId]) {
            $('input[name="receipt_number"]').val(nextReceipts[pelangganId]);
        } else {
            $('input[name="receipt_number"]').val('Otomatis mengikuti ID Pelanggan');
        }
    });

    $('input[name="jumlah_bulan"]').on('change keyup', hitungSemua);

    if ($('select[name="pelanggan_id"]').val()) {
        loadInvoiceInfo($('select[name="pelanggan_id"]').val());
        hitungSemua();
    }
});
</script>
@endpush
@endsection