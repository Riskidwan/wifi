@php
    \Carbon\Carbon::setLocale('id');

    $W = 32;
    $divider = str_repeat('-', $W);

    function centerText($text, $width)
    {
        $len = mb_strlen($text);
        if ($len >= $width)
            return $text;
        $pad = intval(($width - $len) / 2);
        return str_repeat(' ', $pad) . $text;
    }

    function lrLine($left, $right, $width)
    {
        $space = max(1, $width - mb_strlen($left) - mb_strlen($right));
        return $left . str_repeat(' ', $space) . $right;
    }

    $companyName = $billingConfig->company_name ?? 'PEMALANG';
    $companyAddress = $billingConfig->company_address ?? '';
    $companyPhone = $billingConfig->company_phone ?? '0895800439251';

    $paket = $payment->pelanggan->paket;
    if ($paket) {
        $hargaDasar = $paket->harga;
        $diskonPersen = $paket->diskon_aktif ? ($paket->diskon_persen ?? 0) : 0;
        $ppnPersen = $paket->ppn_aktif ? ($paket->ppn_persen ?? 11) : 0;
        $diskon = $hargaDasar * ($diskonPersen / 100);
        $ppn = $hargaDasar * ($ppnPersen / 100);
        $total = $hargaDasar + $ppn - $diskon;
        $namaPaket = $paket->nama_paket;
    } else {
        $hargaDasar = $payment->amount_paid;
        $diskonPersen = 0;
        $ppnPersen = 0;
        $diskon = 0;
        $ppn = 0;
        $total = $payment->amount_paid;
        $namaPaket = '-';
    }

    $fHarga = 'Rp.' . number_format($hargaDasar, 0, ',', '.');
    $fPpn = 'Rp.' . number_format($ppn, 0, ',', '.');
    $fDiskon = 'Rp.' . number_format($diskon, 0, ',', '.');
    $fTotal = 'Rp ' . number_format($total, 0, ',', '.');
    $fUang = $payment->uang_dibayar ? 'Rp ' . number_format($payment->uang_dibayar, 0, ',', '.') : '';
    $fKembali = $payment->kembalian !== null ? 'Rp ' . number_format($payment->kembalian, 0, ',', '.') : '';

    $periode = now()->translatedFormat('F Y');
    $tanggal = now()->translatedFormat('d F Y H:i');

    $lines = [];
    $lines[] = centerText($companyName, $W);
    if ($companyAddress)
        $lines[] = centerText($companyAddress, $W);
    $lines[] = centerText('CS: ' . $companyPhone, $W);
    $lines[] = $divider;
    $lines[] = centerText('BUKTI PEMBAYARAN', $W);
    $lines[] = 'No. Struk: ' . $payment->receipt_number;
    $lines[] = 'Tanggal  : ' . $tanggal;
    $lines[] = $divider;
    $lines[] = 'ID PEL: ' . $payment->pelanggan->kode_pelanggan;
    $lines[] = 'Nama  : ' . $payment->pelanggan->nama_pelanggan;
    $lines[] = $divider;
    $lines[] = 'Periode: ' . $periode;
    $lines[] = $divider;
    $lines[] = lrLine('Paket', $namaPaket, $W);
    $lines[] = lrLine('Harga', $fHarga, $W);
    if ($ppnPersen > 0)
        $lines[] = lrLine('PPN ' . $ppnPersen . '%', $fPpn, $W);
    if ($diskonPersen > 0)
        $lines[] = lrLine('Diskon ' . $diskonPersen . '%', $fDiskon, $W);
    $lines[] = lrLine('TOTAL', $fTotal, $W);
    if ($payment->uang_dibayar) {
        $lines[] = $divider;
        $lines[] = lrLine('Uang Dibayar', $fUang, $W);
        $lines[] = lrLine('Kembalian', $fKembali, $W);
    }
    $lines[] = $divider;
    $lines[] = 'Metode: ' . ucfirst($payment->payment_method);
    if ($payment->reference_number)
        $lines[] = 'Ref   : ' . $payment->reference_number;
    $lines[] = 'Kasir : ' . $payment->cashier_name;
    $lines[] = $divider;
    $lines[] = centerText('Terima kasih', $W);
    $lines[] = '';
    $lines[] = '';
    $lines[] = '';

    $receiptText = implode("\n", $lines);

    // URL JSON untuk Bluetooth Print App
    $jsonUrl = route('payments.receipt.json', $payment->id);
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Pembayaran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #eee;
            font-family: sans-serif;
        }

        .receipt-box {
            background: #fff;
            padding: 12px;
            border: 1px solid #bbb;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 320px;
        }

        pre.struk {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.6;
            color: #000;
            white-space: pre;
            overflow-x: auto;
            -webkit-font-smoothing: none;
            text-rendering: optimizeSpeed;
        }

        .aksi {
            margin-top: 15px;
            text-align: center;
            width: 100%;
            max-width: 320px;
        }

        .aksi a,
        .aksi button {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: bold;
            margin: 6px 0;
            cursor: pointer;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            border: none;
        }

        .btn-bt {
            background: #4CAF50;
            color: #fff;
            font-size: 17px;
        }

        .btn-print {
            background: #2196F3;
            color: #fff;
        }

        .btn-kembali {
            background: #777;
            color: #fff;
        }

        .info-app {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
            text-align: center;
            max-width: 320px;
        }

        .info-app a {
            color: #2196F3;
        }

        /* ===== PRINT MODE ===== */
        @page {
            size: 48mm auto;
            margin: 0;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                display: block;
            }

            .receipt-box {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
                max-width: none;
                width: 48mm;
            }

            pre.struk {
                font-size: calc(48mm / 38);
                font-weight: 900;
                line-height: 1.5;
                overflow: visible;
            }

            .aksi,
            .info-app {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-box">
        <pre class="struk">{{ $receiptText }}</pre>
    </div>

    <div class="aksi">
        {{-- Tombol utama: Cetak via Bluetooth Print App (Android) --}}
        <a class="btn-bt" href="my.bluetoothprint.scheme://{{ $jsonUrl }}">
            📶 Cetak Thermal (Bluetooth)
        </a>

        {{-- Tombol cadangan: Print dialog browser biasa --}}
        <button class="btn-print" onclick="window.print()">🖨️ Cetak (Print Dialog)</button>

        <a class="btn-kembali" href="{{ route('payments.index') }}">🔙 Kembali</a>
    </div>

    <div class="info-app">
        📱 Tombol "Cetak Thermal" memerlukan aplikasi
        <a href="https://play.google.com/store/apps/details?id=mate.bluetoothprint" target="_blank">Bluetooth Print</a>
        di HP Android. Aktifkan fitur <b>Browser Print</b> di dalam aplikasi tersebut.
    </div>

</body>

</html>