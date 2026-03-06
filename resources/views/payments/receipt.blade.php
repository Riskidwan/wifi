@php
    \Carbon\Carbon::setLocale('id');

    $W = 32; // Lebar karakter kertas thermal (32 kolom standar 58mm)
    $divider = str_repeat('.', $W);

    // Helper: Rata tengah
    function centerText($text, $width)
    {
        $pad = max(0, intval(($width - mb_strlen($text)) / 2));
        return str_repeat(' ', $pad) . $text;
    }

    // Helper: Baris kiri-kanan (label ... nilai)
    function lrLine($left, $right, $width)
    {
        $space = max(1, $width - mb_strlen($left) - mb_strlen($right));
        return $left . str_repeat(' ', $space) . $right;
    }

    // Data Perusahaan
    $companyName = $billingConfig->company_name ?? 'PEMALANG';
    $companyAddress = $billingConfig->company_address ?? '';
    $companyPhone = $billingConfig->company_phone ?? '0895800439251';

    // Data Paket
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

    // Format angka
    $fHarga = 'Rp.' . number_format($hargaDasar, 0, ',', '.');
    $fPpn = 'Rp.' . number_format($ppn, 0, ',', '.');
    $fDiskon = 'Rp.' . number_format($diskon, 0, ',', '.');
    $fTotal = 'Rp ' . number_format($total, 0, ',', '.');
    $fUang = $payment->uang_dibayar ? 'Rp ' . number_format($payment->uang_dibayar, 0, ',', '.') : '';
    $fKembali = $payment->kembalian !== null ? 'Rp ' . number_format($payment->kembalian, 0, ',', '.') : '';

    // Periode
    $periode = now()->translatedFormat('F Y');
    $tanggal = now()->translatedFormat('d F Y H:i');

    // Bangun struk sebagai teks murni
    $lines = [];
    $lines[] = centerText($companyName, $W);
    if ($companyAddress) {
        $lines[] = centerText($companyAddress, $W);
    }
    $lines[] = centerText('CS: ' . $companyPhone, $W);
    $lines[] = $divider;
    $lines[] = centerText('BUKTI PEMBAYARAN', $W);
    $lines[] = centerText('No. Struk: ' . $payment->receipt_number, $W);
    $lines[] = centerText('Tanggal: ' . $tanggal, $W);
    $lines[] = $divider;
    $lines[] = 'ID PEL: ' . $payment->pelanggan->kode_pelanggan;
    $lines[] = 'Nama  : ' . $payment->pelanggan->nama_pelanggan;
    $lines[] = $divider;
    $lines[] = 'Periode: ' . $periode;
    $lines[] = $divider;
    $lines[] = lrLine('Paket', $namaPaket, $W);
    $lines[] = lrLine('Harga', $fHarga, $W);
    if ($ppnPersen > 0) {
        $lines[] = lrLine('PPN ' . $ppnPersen . '%', $fPpn, $W);
    }
    if ($diskonPersen > 0) {
        $lines[] = lrLine('Diskon ' . $diskonPersen . '%', $fDiskon, $W);
    }
    $lines[] = lrLine('TOTAL', $fTotal, $W);

    if ($payment->uang_dibayar) {
        $lines[] = $divider;
        $lines[] = lrLine('Uang Dibayar', $fUang, $W);
        $lines[] = lrLine('Kembalian', $fKembali, $W);
    }

    $lines[] = $divider;
    $lines[] = 'Metode: ' . ucfirst($payment->payment_method);
    if ($payment->reference_number) {
        $lines[] = 'Ref   : ' . $payment->reference_number;
    }
    $lines[] = 'Kasir : ' . $payment->cashier_name;
    $lines[] = $divider;
    $lines[] = centerText('Terima kasih', $W);

    $receiptText = implode("\n", $lines);
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Struk Pembayaran</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            background: #f4f4f4;
        }

        .receipt-wrapper {
            background: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        pre.receipt {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.6;
            color: #000;
            margin: 0;
            white-space: pre;
            /* Matikan anti-aliasing agar teks TAJAM di printer thermal */
            -webkit-font-smoothing: none;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeSpeed;
        }

        .no-print {
            margin-top: 15px;
            text-align: center;
        }

        .no-print button,
        .no-print a {
            padding: 8px 20px;
            font-size: 14px;
            margin: 0 5px;
            cursor: pointer;
            border: 1px solid #999;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            background: #eee;
        }

        .no-print button {
            background: #4CAF50;
            color: #fff;
            border-color: #4CAF50;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                display: block;
            }

            .receipt-wrapper {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            pre.receipt {
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-wrapper">
        <pre class="receipt">{{ $receiptText }}</pre>

        <div class="no-print">
            <button onclick="window.print()">🖨️ Cetak Struk</button>
            <a href="{{ route('payments.index') }}">🔙 Kembali</a>
        </div>
    </div>
</body>

</html>