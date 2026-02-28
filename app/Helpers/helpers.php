
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
    $periodStart = \Carbon\Carbon::parse($invoice->billing_period_start)->format('d M Y');
    $periodEnd = \Carbon\Carbon::parse($invoice->billing_period_end)->format('d M Y');
    $dueDate = \Carbon\Carbon::parse($invoice->due_date)->format('d M Y');
    $invoiceDate = \Carbon\Carbon::parse($invoice->created_at)->format('d M Y');
    $amount = $invoice->amount;
    $amountFormatted = number_format($amount, 0, ',', '.');

    // Ambil data perusahaan
    $billingConfig = \App\Models\BillingConfig::first();
    $companyName = $billingConfig->company_name ?? 'MARKISA';
    $companyPhone = $billingConfig->company_phone ?? '0895800439251';
    $companyEmail = $billingConfig->company_email ?? 'markisa@gmail.com';
    $companyAddress = $billingConfig->company_address ?? 'PEMALANG';

    // Hitung PPN (sesuaikan dengan logika sistem kamu)
    $ppnPercent = 11; // atau ambil dari billing config
    $ppnAmount = $amount * ($ppnPercent / 100);
    $totalAmount = $amount + $ppnAmount;

    $ppnAmountFormatted = number_format($ppnAmount, 0, ',', '.');
    $totalAmountFormatted = number_format($totalAmount, 0, ',', '.');

    // Format pesan
    // Format pesan sesuai permintaan baru
    $message = "Tagihan Anda Sudah Terbit\n\n";
    $message .= "Pelanggan Yth.\n";
    $message .= "Bpk/Ibu *{$invoice->pelanggan->nama_pelanggan}*,\n";
    $message .= "ID pelanggan *{$invoice->pelanggan->kode_pelanggan}*\n\n";
    $message .= "Tagihan Anda sudah terbit dengan rincian sebagai berikut.\n";
    $message .= "No. Invoice : *{$invoice->invoice_number}*\n";
    $message .= "Periode : *{$periodStart}*\n"; // Pastikan format periodStart sudah "Maret 2026"
    $message .= "Paket Internet : *{$invoice->paket_nama}*\n";
    $message .= "Jatuh tempo : *{$dueDate}*\n";
    $message .= "Total Tagihan *Rp. {$totalAmountFormatted}*\n\n";
    $message .= "Pembayaran dapat dilakukan melalui transfer ke rekening kami berikut:\n\n";
    $message .= "*BRI an. PT MARKISA TECHNOLOGY*\n";
    $message .= "*No. Rek. 0069-01-555666-56-3*\n\n";
    $message .= "Mohon melakukan pembayaran sebelum tanggal jatuh tempo agar layanan Anda tetap aktif.\n\n";
    $message .= "kirimkan bukti transfer ke nomer yang tertera dibawah ini untuk konfirmasi pembayaran.\n\n";
    $message .= "Customer Service : 081572024200\n\n";
    $message .= "Terima kasih,\n";
    $message .= "*MARKISANET, caur*";
    return $message;
}


?>