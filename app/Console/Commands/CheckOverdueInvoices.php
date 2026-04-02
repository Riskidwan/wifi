<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Services\MikrotikService;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Tandai invoice overdue & nonaktifkan pelanggan yang lewat jatuh tempo';

    public function handle()
    {
        $today = now()->startOfDay();
        $mikrotik = new MikrotikService();

        // 1. Tandai semua invoice unpaid yang sudah lewat jatuh tempo menjadi overdue
        $overdueCount = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'overdue']);

        if ($overdueCount > 0) {
            $this->info("Menandai {$overdueCount} invoice sebagai overdue.");
        }

        // 2. Nonaktifkan pelanggan yang punya invoice overdue
        $pelanggans = Pelanggan::where('status_akun', 'active')
            ->whereHas('invoices', function ($q) use ($today) {
            $q->where('status', 'overdue');
        })
            ->get();

        if ($pelanggans->isEmpty()) {
            $this->info('Tidak ada pelanggan yang perlu dinonaktifkan.');
            return;
        }

        foreach ($pelanggans as $pelanggan) {
            // Update status di database
            $pelanggan->update(['status_akun' => 'inactive']);

            // Disable PPPoE di MikroTik
            $mikrotik->updatePPPoESecretStatus($pelanggan->username_pppoe, 'inactive');

            $this->info("Nonaktifkan: {$pelanggan->nama_pelanggan} ({$pelanggan->username_pppoe})");
        }

        $this->info("Selesai. {$pelanggans->count()} pelanggan dinonaktifkan.");
    }
}