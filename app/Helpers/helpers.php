
<?php function formatBytes($bytes, $decimal = null)
{
    $satuan = ['Bytes', 'Kb', 'Mb', 'Gb', 'Tb'];
    $i = 0;
    while ($bytes > 1024) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $decimal) . '-' . $satuan[$i];
}


function formatWaNumber($phone)
{
    if (!$phone)
        return '';
    $phone = preg_replace('/[^\d]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        return '62' . substr($phone, 1);
    }
    if (substr($phone, 0, 2) !== '62') {
        return '62' . $phone;
    }
    return $phone;
}

function getInvoiceWaMessage($invoice)
{
    $periodStart = \Carbon\Carbon::parse($invoice->billing_period_start)->translatedFormat('F Y');
    $periodEnd = \Carbon\Carbon::parse($invoice->billing_period_end)->translatedFormat('d F Y');
    $dueDate = \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y');
    $invoiceDate = \Carbon\Carbon::parse($invoice->created_at)->format('d M Y');
    $amount = $invoice->amount;
    $amountFormatted = number_format($amount, 0, ',', '.');

    // Ambil data perusahaan
    $billingConfig = \App\Models\BillingConfig::first();
    $companyName = $billingConfig->company_name ?? 'MARKISA';
    $companyPhone = $billingConfig->company_phone ?? '0895800439251';
    $companyEmail = $billingConfig->company_email ?? 'markisa@gmail.com';
    $companyAddress = $billingConfig->company_address ?? 'PEMALANG';

    // Hitung PPN & Diskon dari paket
    $paket = \App\Models\Paket::where('nama_paket', $invoice->paket_nama)->first();
    $ppnPersen = ($paket && $paket->ppn_aktif) ? $paket->ppn_persen : 0;
    $diskonPersen = ($paket && $paket->diskon_aktif) ? $paket->diskon_persen : 0;

    $ppnAmount = $amount * ($ppnPersen / 100);
    $diskonAmount = $amount * ($diskonPersen / 100);
    $totalAmount = $invoice->total_amount ?? ($amount + $ppnAmount - $diskonAmount);

    $totalAmountFormatted = number_format($totalAmount, 0, ',', '.');

    // Ambil template dari config atau gunakan default
    $template = $billingConfig->wa_invoice_template;
    
    if (!$template) {
        $template = "Tagihan Anda Sudah Terbit\n\n";
        $template .= "Pelanggan Yth.\n";
        $template .= "Bpk/Ibu *[nama_pelanggan]*,\n";
        $template .= "ID pelanggan *[id_pelanggan]*\n\n";
        $template .= "Tagihan Anda sudah terbit dengan rincian sebagai berikut.\n";
        $template .= "No. Invoice : *[no_invoice]*\n";
        $template .= "Periode : *[periode]*\n";
        $template .= "Paket Internet : *[paket]*\n";
        $template .= "Jatuh tempo : *[jatuh_tempo]*\n";
        $template .= "Total Tagihan *Rp. [total_tagihan]*\n\n";
        $template .= "[rekening_pembayaran]";
        $template .= "Mohon melakukan pembayaran sebelum tanggal jatuh tempo agar layanan Anda tetap aktif.\n\n";
        $template .= "kirimkan bukti transfer ke nomer yang tertera dibawah ini untuk konfirmasi pembayaran.\n\n";
        $template .= "Customer Service : 081572024200\n\n";
        $template .= "Terima kasih,\n";
        $template .= "*MARKISANET, caur*";
    }

    // Siapkan data untuk placeholder
    $rekening = "";
    if ($invoice->pelanggan->norekening_briva) {
        $rekening .= "*BRI an. {$invoice->pelanggan->nama_pelanggan}*\n";
        $rekening .= "*No. Rek. {$invoice->pelanggan->norekening_briva}*\n";
    }

    // Mapping placeholder ke nilai asli
    $placeholders = [
        '[nama_pelanggan]' => $invoice->pelanggan->nama_pelanggan,
        '[id_pelanggan]' => $invoice->pelanggan->kode_pelanggan,
        '[no_invoice]' => $invoice->invoice_number,
        '[periode]' => $periodStart,
        '[paket]' => $invoice->paket_nama,
        '[jatuh_tempo]' => $dueDate,
        '[total_tagihan]' => $totalAmountFormatted,
        '[rekening_pembayaran]' => $rekening,
    ];

    // Ganti semua placeholder
    $message = str_replace(array_keys($placeholders), array_values($placeholders), $template);

    return $message;
}


?>