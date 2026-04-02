<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>

<style>
    /* ================= GLOBAL ================= */
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 12px;
        color: #333;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 100%;
        padding: 30px;
        position: relative;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
    }

    th, td {
        font-size: 11px;
        padding: 7px;
        vertical-align: top;
    }

    .text-right { text-align: right; }

    /* ================= STAMP ================= */
    .stamp-container {
        position: fixed;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        z-index: 999;
        pointer-events: none;
    }

    .stamp {
        display: inline-block;
        padding: 15px 40px;
        border-radius: 12px;
        font-size: 48px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 4px;
        opacity: 0.15;
        border: 6px solid;
    }

    .stamp-lunas {
        color: #28a745;
        border-color: #28a745;
    }

    .stamp-belum-lunas {
        color: #dc3545;
        border-color: #dc3545;
    }

    /* ================= HEADER ================= */
    .company-name {
        font-size: 26px;
        font-weight: bold;
        color: #2d63ff;
    }

    .invoice-title {
        text-align: right;
    }

    .invoice-title h2 {
        margin: 0;
        font-size: 22px;
        color: #2d63ff;
    }

    .meta div {
        font-size: 11px;
        margin-top: 4px;
    }

    /* ================= STATUS BADGE ================= */
    .status-badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        color: #fff;
        margin-top: 6px;
    }
    .badge-paid { background: #28a745; }
    .badge-unpaid { background: #dc3545; }
    .badge-overdue { background: #e67e22; }

    /* ================= SECTION ================= */
    .section-title {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 2px solid #ddd;
    }

    .label {
        width: 80px;
        font-weight: bold;
        padding: 4px 0;
    }

    .value {
        padding: 4px 0;
    }

    /* ================= INFO TABLE ================= */
    .info-table td {
        border: none;
        padding: 3px 0;
    }

    /* ================= ITEM TABLE ================= */
    .item-table th {
        background: #2c3e50;
        color: #fff;
        text-align: left;
    }

    .item-table td {
        border-bottom: 1px solid #ddd;
    }

    /* ================= SIGNATURE ================= */
    .signature {
        margin-top: 50px;
        width: 40%;
        margin-left: auto;
        text-align: center;
    }

    .signature-text {
        font-size: 11px;
        margin-bottom: 8px;
    }

    .signature-box {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
    }

    .signature-box img {
        max-height: 70px;
        max-width: 100%;
    }

    .signature-name {
        font-weight: bold;
        font-size: 11px;
    }

    .signature-role {
        font-size: 10px;
        color: #555;
    }

    /* ================= PAYMENT INFO ================= */
    .payment-info {
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #28a745;
        border-radius: 6px;
        background: #f0fff4;
    }

    .payment-info td {
        border: none;
        padding: 3px 6px;
        font-size: 11px;
    }
</style>

</head>
<body>

<div class="container">

{{-- ===== STAMP WATERMARK ===== --}}
<div class="stamp-container">
    @if($invoice->status === 'paid')
        <div class="stamp stamp-lunas">LUNAS</div>
    @else
        <div class="stamp stamp-belum-lunas">BELUM LUNAS</div>
    @endif
</div>

<!-- HEADER -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <!-- KIRI : LOGO + NAMA -->
        <td valign="middle">
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <div class="company-name">
                            {{ $billingConfig->company_name }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        @if(file_exists(public_path('logo/logo.jpeg')))
                        <img src="{{ public_path('logo/logo.jpeg') }}"
                             alt="Logo"
                             style="height:50px; display:block; margin-bottom:6px;">
                        @endif
                    </td>
                </tr>
            </table>
        </td>

        <!-- KANAN : INVOICE INFO -->
        <td valign="middle" align="right">
            <div class="invoice-title">
                @if($invoice->status === 'paid')
                    <h2>Invoice Pembayaran</h2>
                @else
                    <h2>Invoice Tagihan</h2>
                @endif
                <div class="meta">
                    <div>Referensi : {{ $invoice->invoice_number }}</div>
                    <div>Tanggal : {{ \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('d F Y') }}</div>
                    <div>Jatuh Tempo : {{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</div>
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- INFO -->
<table style="margin-top:20px;">
    <tr>
        <td width="50%" style="padding-right:20px;">
            <div class="section-title">Informasi Perusahaan</div>
            <table class="info-table">
                <tr><td class="label">Nama</td><td class="value">{{ $billingConfig->company_name }}</td></tr>
                <tr><td class="label">Alamat</td><td class="value">{{ $billingConfig->company_address ?? '-' }}</td></tr>
                <tr><td class="label">Tel</td><td class="value">{{ $billingConfig->company_phone ?? '-' }}</td></tr>
                <tr><td class="label">Email</td><td class="value">{{ $billingConfig->company_email ?? '-' }}</td></tr>
            </table>
        </td>
        <td width="50%" style="padding-left:20px;">
            <div class="section-title">Tagihan Kepada</div>
            <table class="info-table">
                <tr><td class="label">ID</td><td class="value">{{ $invoice->pelanggan->kode_pelanggan }}</td></tr>
                <tr><td class="label">Nama</td><td class="value">{{ $invoice->pelanggan->nama_pelanggan }}</td></tr>
                <tr><td class="label">Alamat</td><td class="value">{{ $invoice->pelanggan->alamat ?? '-' }}</td></tr>
                <tr><td class="label">Tel</td><td class="value">{{ $invoice->pelanggan->no_hp ?? '-' }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<!-- ITEM -->
<table class="item-table" style="margin-top:25px;">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Deskripsi</th>
            <th>Kuantitas</th>
            <th>Harga</th>
            <th>Diskon</th>
            <th>PPN</th>
            <th class="text-right">Jumlah</th>
        </tr>
    </thead>
    <tbody>
    @php
        $paket = \App\Models\Paket::where('nama_paket', $invoice->paket_nama)->first();
        
        $diskon_aktif = $paket ? $paket->diskon_aktif : false;
        $diskon_persen = $paket ? $paket->diskon_persen : 0;
        $ppn_aktif = $paket ? $paket->ppn_aktif : false;
        $ppn_persen = $paket ? $paket->ppn_persen : 11;

        $diskon_value = $diskon_aktif ? ($invoice->amount * $diskon_persen / 100) : 0;
        $ppn_value = $ppn_aktif ? ($invoice->amount * $ppn_persen / 100) : 0;
        $total = $invoice->total_amount ?? ($invoice->amount + $ppn_value - $diskon_value);

        $periode = \Carbon\Carbon::parse($invoice->billing_period_start)->translatedFormat('F Y');
    @endphp

    <tr>
        <td>{{ $invoice->paket_nama }}</td>
        <td>Internet Bulanan — {{ $periode }}</td>
        <td class="text-right">1</td>
        <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
        <td class="text-right">{{ $diskon_aktif ? $diskon_persen.'%' : '–' }}</td>
        <td class="text-right">{{ $ppn_aktif ? $ppn_persen.'%' : '–' }}</td>
        <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
    </tr>
</tbody>
</table>

<!-- BOTTOM -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;">
    <tr>
        <!-- KIRI -->
        <td width="60%"></td>

        <!-- KANAN : SUMMARY -->
        <td width="40%" valign="top">
            <div class="section-title">Ringkasan</div>

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Subtotal</td>
                    <td align="right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon {{ $diskon_aktif ? '('.$diskon_persen.'%)' : '' }}</td>
                    <td align="right">- Rp {{ number_format($diskon_value, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>PPN {{ $ppn_aktif ? '('.$ppn_persen.'%)' : '' }}</td>
                    <td align="right">Rp {{ number_format($ppn_value, 0, ',', '.') }}</td>
                </tr>
                <tr style="font-weight:bold; font-size:13px; border-top:2px solid #333;">
                    <td style="padding-top:8px;">Total</td>
                    <td align="right" style="padding-top:8px;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>




<div class="signature">
    <div class="signature-text">Dengan hormat,</div>

    <div class="signature-box">
        @if(file_exists(public_path('ttd.png')))
            <img src="{{ public_path('ttd.png') }}" alt="Tanda Tangan">
        @else
            <span>Tanda tangan</span>
        @endif
    </div>

    <div class="signature-name">{{ $billingConfig->company_name }}</div>
    <div class="signature-role">Finance Dept</div>
</div>


</div>
</body>
</html>
