
<?php function formatBytes($bytes, $decimal = null){
    $satuan = ['Bytes', 'Kb', 'Mb', 'Gb', 'Tb'];
    $i = 0;
    while ($bytes > 1024) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $decimal) .'-' . $satuan[$i];
}


function formatWaNumber($phone) {
    if (!$phone) return '';
    $phone = preg_replace('/[^\d]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        return '62' . substr($phone, 1);
    }
    if (substr($phone, 0, 2) !== '62') {
        return '62' . $phone;
    }
    return $phone;
}

function getInvoiceWaMessage($invoice) {
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
    $message = "📱 *{$companyName}*\n";
    $message .= "Jl. {$companyAddress}\n";
    $message .= "📞 {$companyPhone} | ✉️ {$companyEmail}\n\n";

    $message .= "📋 *INVOICE TAGIHAN*\n";
    $message .= "No. Invoice: *{$invoice->invoice_number}*\n";
    $message .= "Tanggal: {$invoiceDate}\n";
    $message .= "Pelanggan: *{$invoice->pelanggan->nama_pelanggan}*\n";
    $message .= "Kode: {$invoice->pelanggan->kode_pelanggan}\n\n";

    $message .= "📦 *DETAIL TAGIHAN*\n";
    $message .= "Paket Internet: *{$invoice->paket_nama}*\n";
    $message .= "Periode: {$periodStart} - {$periodEnd}\n";
    $message .= "Jumlah: Rp {$amountFormatted}\n\n";

    $message .= "🧾 *RINGKASAN*\n";
    $message .= "Subtotal: Rp {$amountFormatted}\n";
    $message .= "PPN ({$ppnPercent}%): Rp {$ppnAmountFormatted}\n";
    $message .= "*Total: Rp {$totalAmountFormatted}*\n\n";

    $message .= "⏰ *JATUH TEMPO*\n";
    $message .= "Tanggal: *{$dueDate}*\n\n";

    $message .= "💳 *INFORMASI PEMBAYARAN*\n";
    $message .= "Transfer ke rekening:\n";
    $message .= "🏦 BCA 1234567890\n";
    $message .= "💳 a.n {$companyName}\n\n";

    $message .= "📲 *KONFIRMASI*\n";
    $message .= "Setelah bayar, konfirmasi ke nomor ini dengan:\n";
    $message .= "1. Nama pelanggan\n";
    $message .= "2. Tanggal & jumlah transfer\n";
    $message .= "3. Bukti transfer (screenshot)\n\n";

    $message .= "📄 *INVOICE RESMI*\n";
    $message .= "Download invoice PDF:\n";
    $message .= "https://isp-markisa.com/invoices/{$invoice->id}/preview/advanced\n\n";

    $message .= "🙏 Terima kasih atas kepercayaan Anda!\n";
    $message .= "Jangan ragu hubungi kami untuk pertanyaan apa pun.";

    return $message;
}


?>