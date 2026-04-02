<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Models\BillingConfig; // Pastikan ini ada
use Carbon\Carbon;
use App\Models\Paket;

class BillingService
{

    public function generateMonthlyInvoices()
    {
        $billingConfig = BillingConfig::first();
        $dueDays = $billingConfig->due_days_after_period ?? 5;

        $pelanggans = Pelanggan::where('status_akun', 'active')
            ->with('paket')
            ->get();

        $invoices = [];
        $today = Carbon::today();

        // Hanya generate untuk BULAN INI
        $periodStart = $today->copy()->startOfMonth();
        $periodEnd = $today->copy()->endOfMonth();
        // Jatuh tempo = tanggal tertentu di bulan yang sama (misalnya tanggal 5)
        $dueDate = $periodStart->copy()->addDays($dueDays - 1);

        \Illuminate\Support\Facades\DB::transaction(function () use (&$invoices, $pelanggans, $periodStart, $periodEnd, $dueDate, $today) {
            foreach ($pelanggans as $pelanggan) {
                if (!$pelanggan->paket)
                    continue;

                // Cek apakah sudah ada invoice untuk bulan ini
                $existing = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                    ->where('billing_period_start', $periodStart)
                    ->first();

                if ($existing)
                    continue; // Sudah ada → skip

                // Cek apakah sudah pernah bayar untuk bulan ini
                $alreadyPaid = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                    ->where('billing_period_start', $periodStart)
                    ->where('status', 'paid')
                    ->exists();

                if ($alreadyPaid)
                    continue;
                
                // Ambil paket
                $paket = Paket::where('nama_paket', $pelanggan->paket->nama_paket)->first();
                if (!$paket)
                    continue;

                // Hitung total akhir
                $hargaDasar = $pelanggan->paket->harga ?? 0;
                $ppn = 0;
                $diskon = 0;

                if ($paket->ppn_aktif) {
                    $ppn = $hargaDasar * ($paket->ppn_persen / 100);
                }
                if ($paket->diskon_aktif) {
                    $diskon = $hargaDasar * ($paket->diskon_persen / 100);
                }

                $totalAmount = $hargaDasar + $ppn - $diskon;


                // Format: INV-{ID PEL}{NO URUT}{BULAN}{TAHUN} → contoh: INV-0003010326
                $pelangganInvoiceCount = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)->count() + 1;
                $invoiceNumber = 'INV-' . $pelanggan->kode_pelanggan . str_pad($pelangganInvoiceCount, 2, '0', STR_PAD_LEFT) . $today->format('m') . $today->format('y');

                try {
                    $invoice = Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'pelanggan_id' => $pelanggan->id_pelanggan,
                        'paket_nama' => $pelanggan->paket->nama_paket,
                        'amount' => $pelanggan->paket->harga ?? 0,
                        'total_amount' => $totalAmount,
                        'billing_period_start' => $periodStart,
                        'billing_period_end' => $periodEnd,
                        'due_date' => $dueDate,
                        'status' => 'unpaid'
                    ]);

                    $invoices[] = $invoice;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Jika kena unique constraint (race condition), skip saja
                    if ($e->getCode() == 23000) {
                        continue;
                    }
                    throw $e;
                }
            }
        });

        return $invoices;
    }
}