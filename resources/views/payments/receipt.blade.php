@php
    \Carbon\Carbon::setLocale('id');

    $W = 32; // 32 karakter per baris (standar Mini 5809DD)
    $divider = str_repeat('-', $W);

    // Helper: Rata tengah
    function centerText($text, $width)
    {
        $len = mb_strlen($text);
        if ($len >= $width)
            return $text;
        $pad = intval(($width - $len) / 2);
        return str_repeat(' ', $pad) . $text;
    }

    // Helper: Baris kiri-kanan
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

    $periode = now()->translatedFormat('F Y');
    $tanggal = now()->translatedFormat('d F Y H:i');

    // Bangun struk
    $lines = [];
    $lines[] = centerText($companyName, $W);
    if ($companyAddress) {
        $lines[] = centerText($companyAddress, $W);
    }
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
    $lines[] = '';
    $lines[] = '';
    $lines[] = '';

    $receiptText = implode("\n", $lines);
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Pembayaran</title>
    <style>
        body {
            margin: 0;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f0f0f0;
            font-family: sans-serif;
        }

        .receipt-wrapper {
            background: #fff;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            max-width: 300px;
        }

        pre.receipt {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.5;
            color: #000;
            margin: 0;
            white-space: pre;
            -webkit-font-smoothing: none;
            text-rendering: optimizeSpeed;
        }

        .btn-group {
            margin-top: 15px;
            text-align: center;
        }

        .btn-group button,
        .btn-group a {
            display: inline-block;
            padding: 10px 18px;
            font-size: 14px;
            margin: 5px;
            cursor: pointer;
            border: 1px solid #999;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            background: #eee;
        }

        .btn-print {
            background: #4CAF50;
            color: #fff;
            border-color: #4CAF50;
        }

        .btn-bt {
            background: #2196F3;
            color: #fff;
            border-color: #2196F3;
        }

        .btn-back {
            background: #777;
            color: #fff;
            border-color: #777;
        }

        #btStatus {
            margin-top: 8px;
            font-size: 13px;
            text-align: center;
            color: #555;
        }

        /* ===== PRINT MODE: Dioptimasi untuk kertas 48mm ===== */
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

            .receipt-wrapper {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
                max-width: none;
                width: 48mm;
            }

            pre.receipt {
                font-size: 8pt;
                line-height: 1.4;
                font-weight: bold;
            }

            .btn-group,
            #btStatus {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-wrapper">
        <pre class="receipt" id="receiptContent">{{ $receiptText }}</pre>
    </div>

    <div class="btn-group no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak (Print Dialog)</button>
        <button class="btn-bt" onclick="printViaBluetooth()">📶 Cetak via Bluetooth</button>
        <a class="btn-back" href="{{ route('payments.index') }}">🔙 Kembali</a>
    </div>
    <div id="btStatus"></div>

    <script>
        /**
         * =====================================================
         * RAW ESC/POS Bluetooth Print untuk Mini 5809DD
         * Menggunakan Web Bluetooth API (Chrome/Edge)
         * Printer menerima TEKS MURNI, bukan gambar!
         * =====================================================
         */
        async function printViaBluetooth() {
            const statusEl = document.getElementById('btStatus');

            // Cek support Web Bluetooth
            if (!navigator.bluetooth) {
                statusEl.innerHTML = '❌ Browser tidak mendukung Web Bluetooth.<br>Gunakan <b>Google Chrome</b> versi terbaru.';
                return;
            }

            try {
                statusEl.textContent = '🔍 Mencari printer Bluetooth...';

                // Minta user pilih perangkat Bluetooth
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb', '49535343-fe7d-4ae5-8fa9-9fafd205e455', 'e7810a71-73ae-499d-8c15-faa9aef0c3f2']
                });

                statusEl.textContent = '📡 Menghubungkan ke ' + device.name + '...';
                const server = await device.gatt.connect();

                // Cari service dan characteristic yang bisa ditulis
                let writeChar = null;
                const services = await server.getPrimaryServices();

                for (const service of services) {
                    try {
                        const chars = await service.getCharacteristics();
                        for (const c of chars) {
                            if (c.properties.write || c.properties.writeWithoutResponse) {
                                writeChar = c;
                                break;
                            }
                        }
                        if (writeChar) break;
                    } catch (e) { /* skip service */ }
                }

                if (!writeChar) {
                    statusEl.textContent = '❌ Tidak ditemukan service write pada printer.';
                    server.disconnect();
                    return;
                }

                statusEl.textContent = '📝 Mengirim data struk...';

                // Ambil teks struk
                const text = document.getElementById('receiptContent').textContent;

                // Bangun perintah ESC/POS
                const encoder = new TextEncoder();
                const ESC = 0x1B;
                const GS = 0x1D;
                const LF = 0x0A;

                // Command: Reset + Bold ON + Align Left
                const init = new Uint8Array([ESC, 0x40]); // ESC @ = Init
                const boldOn = new Uint8Array([ESC, 0x45, 0x01]); // ESC E 1 = Bold ON
                const alignLeft = new Uint8Array([ESC, 0x61, 0x00]); // ESC a 0 = Left
                const cutPaper = new Uint8Array([GS, 0x56, 0x00]); // GS V 0 = Full cut
                const feedLines = new Uint8Array([ESC, 0x64, 0x03]); // ESC d 3 = Feed 3 lines

                // Gabungkan semua data
                const textBytes = encoder.encode(text);
                const allData = new Uint8Array([
                    ...init,
                    ...boldOn,
                    ...alignLeft,
                    ...textBytes,
                    ...feedLines,
                    ...cutPaper
                ]);

                // Kirim per 20 byte (BLE limit)
                const CHUNK = 20;
                for (let i = 0; i < allData.length; i += CHUNK) {
                    const chunk = allData.slice(i, i + CHUNK);
                    if (writeChar.properties.writeWithoutResponse) {
                        await writeChar.writeValueWithoutResponse(chunk);
                    } else {
                        await writeChar.writeValue(chunk);
                    }
                    // Delay kecil antar chunk
                    await new Promise(r => setTimeout(r, 30));
                }

                statusEl.textContent = '✅ Struk berhasil dicetak via Bluetooth!';

                // Putuskan koneksi
                setTimeout(() => { server.disconnect(); }, 2000);

            } catch (err) {
                if (err.name === 'NotFoundError') {
                    statusEl.textContent = '⚠️ Tidak ada printer yang dipilih.';
                } else {
                    statusEl.textContent = '❌ Gagal: ' + err.message;
                    console.error('BT Print Error:', err);
                }
            }
        }
    </script>
</body>

</html>