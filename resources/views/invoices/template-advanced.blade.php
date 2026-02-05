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

    /* ================= HEADER ================= */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
    }

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

    .edit {
        font-size: 10px;
        color: #007bff;
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

    /* ================= BOTTOM ================= */
    .bottom {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        gap: 20px;
    }

    .notes {
        width: 55%;
    }

    .notes textarea {
        width: 100%;
        height: 80px;
        border: 1px solid #ccc;
        padding: 8px;
        resize: none;
        font-size: 11px;
    }

    .summary {
        width: 40%;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin: 6px 0;
    }

    .summary-total {
        font-weight: bold;
        font-size: 14px;
        border-top: 1px solid #ddd;
        padding-top: 6px;
    }

    /* ================= SIGNATURE ================= */
    .signature{
    margin-top:50px;
    width:40%;
    margin-left:auto;
    text-align:center;
}

.signature-text{
    font-size:11px;
    margin-bottom:8px;
}

.signature-box{
    height:80px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:8px;
}


.signature-box img{
    max-height:70px;
    max-width:100%;
}

.signature-name{
    font-weight:bold;
    font-size:11px;
}

.signature-role{
    font-size:10px;
    color:#555;
}

</style>


</head>
<body>

<div class="container">


<!-- HEADER -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="header-table">
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
            <img src="{{ public_path('logo/logo.jpeg') }}"
                 alt="Logo"
                 style="height:50px; display:block; margin-bottom:6px;">
        </td>
    </tr>
    
</table>

        </td>
        

        <!-- KANAN : INVOICE INFO -->
        <td valign="middle" align="right">
            <div class="invoice-title">
                <h2>Invoice</h2>
                <div class="meta">
                    <div>Referensi : {{ $invoice->invoice_number }}</div>
                    <div>Tanggal : {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</div>
                    <div>Jatuh Tempo : {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
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
                <tr><td class="label">Nama</td><td class="value">{{ $billingConfig->company_name }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Alamat</td><td class="value">{{ $billingConfig->company_address ?? '-' }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Tel</td><td class="value">{{ $billingConfig->company_phone ?? '-' }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Email</td><td class="value">{{ $billingConfig->company_email ?? '-' }} <span class="edit">✏️</span></td></tr>
            </table>
        </td>
        <td width="50%" style="padding-left:20px;">
            <div class="section-title">Tagihan Kepada</div>
            <table class="info-table">
                <tr><td class="label">Nama</td><td class="value">{{ $invoice->pelanggan->nama_pelanggan }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Alamat</td><td class="value">{{ $invoice->pelanggan->alamat ?? '-' }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Tel</td><td class="value">{{ $invoice->pelanggan->no_hp ?? '-' }} <span class="edit">✏️</span></td></tr>
                <tr><td class="label">Email</td><td class="value">{{ $invoice->pelanggan->email ?? '-' }} <span class="edit">✏️</span></td></tr>
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
        // Ambil data paket berdasarkan nama
        $paket = \App\Models\Paket::where('nama_paket', $invoice->paket_nama)->first();
        
        $diskon_aktif = $paket ? $paket->diskon_aktif : false;
        $diskon_persen = $paket ? $paket->diskon_persen : 0;
        $ppn_aktif = $paket ? $paket->ppn_aktif : false;
        $ppn_persen = $paket ? $paket->ppn_persen : 10;

        // Hitung nilai
        $diskon_value = $diskon_aktif ? ($invoice->amount * $diskon_persen / 100) : 0;
        $ppn_value = $ppn_aktif ? (($invoice->amount - $diskon_value) * $ppn_persen / 100) : 0;
        $total = $invoice->amount - $diskon_value + $ppn_value;
    @endphp

    <tr>
        <td>{{ $invoice->paket_nama }}</td>
        <td>Internet Bulanan</td>
        <td class="text-right">1</td>
        <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
        <td class="text-right">{{ $diskon_persen }}%</td>
       <td class="text-right">
    @if($ppn_aktif)
        {{ $ppn_persen }}%
    @else
        –
    @endif
</td>
        <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
    </tr>
</tbody>
</table>

<!-- BOTTOM -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;">
    <tr>
        <!-- KIRI : PESAN -->
       <td width="60%"></td>

        <!-- KANAN : SUMMARY -->
        <td width="40%" valign="top">
            <div class="section-title">Ringkasan</div>

            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="summary-table">
    <tr>
        <td>Subtotal</td>
        <td align="right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Diskon</td>
        <td align="right">Rp {{ number_format($diskon_value, 0, ',', '.') }}</td>
    </tr>
    <tr>
    <td>PPN (@if($ppn_aktif){{ $ppn_persen }}%@else–@endif)</td>
    <td align="right">Rp {{ number_format($ppn_value, 0, ',', '.') }}</td>
</tr>
    <tr class="summary-total">
        <td>Total</td>
        <td align="right">Rp {{ number_format($total, 0, ',', '.') }}</td>
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

    <div class="signature-name">PT Kledo Berhati Nyaman</div>
    <div class="signature-role">Finance Dept</div>
</div>




</div>
</body>
</html>
