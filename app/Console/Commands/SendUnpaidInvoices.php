<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\BillingConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendUnpaidInvoices extends Command
{
    protected $signature = 'invoices:send-unpaid';
    protected $description = 'Kirim reminder WA ke pelanggan yang belum bayar via Fonnte API';

    public function handle()
    {
        // Ambil token dari database
        $config = BillingConfig::first();

        if (!$config || !$config->wa_token) {
            $this->error('Token WhatsApp belum diatur! Silakan konfigurasi di halaman Setting → tab WhatsApp.');
            return;
        }

        $token = $config->wa_token;

        $unpaid = Invoice::where('status', 'unpaid')
            ->where('due_date', '<=', now()->addDays(1)) // 1 hari sebelum jatuh tempo
            ->with('pelanggan')
            ->get();

        if ($unpaid->isEmpty()) {
            $this->info('Tidak ada invoice unpaid yang perlu dikirim reminder.');
            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($unpaid as $invoice) {
            if (!$invoice->pelanggan || !$invoice->pelanggan->no_hp) {
                $this->warn("Skip: {$invoice->invoice_number} - tidak ada nomor HP.");
                continue;
            }

            // Format nomor HP
            $phone = preg_replace('/[^0-9]/', '', $invoice->pelanggan->no_hp);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $message = "⚠️ *REMINDER TAGIHAN*\n"
                . "Pelanggan: {$invoice->pelanggan->nama_pelanggan}\n"
                . "Invoice: {$invoice->invoice_number}\n"
                . "Jumlah: Rp " . number_format($invoice->amount, 0, ',', '.') . "\n"
                . "Jatuh Tempo: {$invoice->due_date->format('d M Y')}\n\n"
                . "Segera lakukan pembayaran!";

            try {
                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

                $data = $response->json();

                if (isset($data['status']) && $data['status'] === true) {
                    $this->info("✅ Kirim WA ke: {$invoice->pelanggan->nama_pelanggan} ({$phone})");
                    $sent++;
                }
                else {
                    $reason = $data['reason'] ?? $data['detail'] ?? 'Unknown error';
                    $this->warn("❌ Gagal kirim ke {$invoice->pelanggan->nama_pelanggan}: {$reason}");
                    $failed++;
                }
            }
            catch (\Exception $e) {
                $this->error("❌ Error kirim ke {$invoice->pelanggan->nama_pelanggan}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Selesai. Berhasil: {$sent}, Gagal: {$failed}");
    }
}