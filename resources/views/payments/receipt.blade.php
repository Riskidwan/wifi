@php \Carbon\Carbon::setLocale('id'); @endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Struk Pembayaran</title>
    <style>
        /* Optimasi Teks Polos untuk Printer Thermal */
        body {
            font-family: 'Courier New', Courier, 'Consolas', monospace;
            font-size: 15px;
            /* Sedikit diperbesar */
            font-weight: 700;
            /* Dibuat tebal (bold) secara default */
            color: #000;
            background: #fff;
            /* Dihitam-putihkan */
            display: flex;
            justify-content: center;
            padding: 20px;

            /* Mematikan fitur penghalusan font dari browser agar tulisan lebih tajam (Teks Murni) */
            -webkit-font-smoothing: none;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeSpeed;
        }

        .receipt-container {
            width: 80mm;
            /* Ukuran standar kertas thermal POS */
            padding: 0;
            margin: 0;
            background: #fff;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: 900;
            font-size: 16px;
        }

        /* Ekstra tebal */
        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        /* Mode Print - Pastikan Browser Tidak Merender Ulang Sebagai Gambar/Blur */
        @media print {
            * {
                color: #000 !important;
                /* Paksa hitam pekat */
                background: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: none;
                padding: 0;
                display: block;
            }

            .receipt-container {
                width: 100%;
                max-width: 80mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="text-center">
            <div class="bold">{{ $billingConfig->company_name ?? 'PEMALANG' }}</div>
            @if($billingConfig && $billingConfig->company_address)
                <div>{{ $billingConfig->company_address }}</div>
            @endif
            <div>CS: {{ $billingConfig->company_phone ?? '0895800439251' }}</div>
            <div class="divider"></div>
            <div class="bold">BUKTI PEMBAYARAN</div>
            <div>No. Struk: {{ $payment->receipt_number }}</div>
            <div>Tanggal: {{ now()->translatedFormat('d F Y H:i') }}</div>
        </div>

        <div class="divider"></div>

        <div>
            <div>ID PEL: {{ $payment->pelanggan->kode_pelanggan }}</div>
            <div>Nama : {{ $payment->pelanggan->nama_pelanggan }}</div>
        </div>

        <div class="divider"></div>

        <!-- Ambil data paket dari pelanggan -->
        @php
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
                $namaPaket = 'Paket Tidak Ditemukan';
            }
        @endphp

        <div>
            <div>Periode: {{ now()->translatedFormat('F Y') }} </div>
        </div>

        <div class="divider"></div>

        <table>
            <tr>
                <td>Paket</td>
                <td class="text-right">{{ $namaPaket }}</td>
            </tr>
            <tr>
                <td>harga</td>
                <td class="text-right">Rp.{{ number_format($hargaDasar, 0, ',', '.') }}</td>
            </tr>

            @if($ppnPersen > 0)
                <tr>
                    <td>ppn {{ $ppnPersen }}%</td>
                    <td class="text-right">Rp.{{ number_format($ppn, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if($diskonPersen > 0)
                <tr>
                    <td>diskon {{ $diskonPersen }}%</td>
                    <td class="text-right">Rp.{{ number_format($diskon, 0, ',', '.') }}</td>
                </tr>
            @endif

            <tr>
                <td class="bold">TOTAL</td>
                <td class="text-right bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </table>

        @if($payment->uang_dibayar)
            <div class="divider"></div>
            <table>
                <tr>
                    <td>Uang Dibayar</td>
                    <td class="text-right">Rp {{ number_format($payment->uang_dibayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembalian</td>
                    <td class="text-right">Rp {{ number_format($payment->kembalian, 0, ',', '.') }}</td>
                </tr>
            </table>
        @endif

        <div class="divider"></div>

        <div>
            <div>Metode: {{ ucfirst($payment->payment_method) }}</div>
            @if($payment->reference_number)
                <div>Ref: {{ $payment->reference_number }}</div>
            @endif
            <div>Kasir: {{ $payment->cashier_name }}</div>
        </div>

        <div class="footer text-center">
            <div>Terima kasih </div>
        </div>

        <!-- Tombol Cetak & Kembali (tidak muncul saat print) -->
        <div class="no-print text-center" style="margin-top: 20px;">
            <button onclick="window.print()" class="btn btn-primary" style="margin-right: 10px;">
                🖨️ Cetak Struk
            </button>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                🔙 Kembali
            </a>
        </div>

    </div>

    <script>
        // Auto print saat halaman dimuat (opsional)
        window.onload = function () {
            // setTimeout(function() {
            //     window.print();
            // }, 1000);
        };
    </script>
</body>

</html>