<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendUnpaidInvoices extends Command
{
    protected $signature = 'invoices:send-unpaid';
    protected $description = 'Kirim reminder WA ke pelanggan yang belum bayar';

    public function handle()
    {
        $unpaid = Invoice::where('status', 'unpaid')
            ->where('due_date', '<=', now()->addDays(1)) // 1 hari sebelum jatuh tempo
            ->with('pelanggan')
            ->get();

        foreach ($unpaid as $invoice) {
            if ($invoice->pelanggan->no_hp) {
                $phone = str_replace(['+', '-', ' '], '', $invoice->pelanggan->no_hp);
                if (substr($phone, 0, 2) !== '62' && strlen($phone) == 10) {
                    $phone = '62895800439251' . substr($phone, 1);
                }

                $message = "⚠️ *REMINDER TAGIHAN*\n"
                         . "Pelanggan: {$invoice->pelanggan->nama_pelanggan}\n"
                         . "Invoice: {$invoice->invoice_number}\n"
                         . "Jumlah: Rp " . number_format($invoice->amount, 0, ',', '.') . "\n"
                         . "Jatuh Tempo: {$invoice->due_date->format('d M Y')}\n\n"
                         . "Segera lakukan pembayaran!";

                // Ganti dengan API WhatsApp Gateway kamu
                Http::post('https://your-wa-gateway.com/send', [
                    'phone' => $phone,
                    'message' => $message
                ]);
                
                $this->info("Kirim WA ke: {$invoice->pelanggan->nama_pelanggan}");
            }
        }
    }
}