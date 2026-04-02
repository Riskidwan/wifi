<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pelanggan;
use App\Services\MikrotikService;
use Carbon\Carbon;

class BillingDisableOverdue extends Command
{
    protected $signature = 'billing:disable-overdue';
    protected $description = 'Disable pelanggan overdue + disable PPPoE di Mikrotik';

   public function handle()
{
    $today = now();

    $mikrotik = new MikrotikService();

    $pelanggans = Pelanggan::where('status_akun', 'active')
        ->whereHas('invoices', function ($q) use ($today) {
            $q->where('status', 'unpaid')
              ->whereDate('due_date', '<', $today);
        })
        ->get();

    if ($pelanggans->isEmpty()) {
        $this->info('Tidak ada pelanggan overdue.');
        return;
    }

    foreach ($pelanggans as $pelanggan) {

        $this->info("Nonaktifkan: {$pelanggan->username_pppoe}");

        // ✅ update DB
        $pelanggan->status_akun = 'inactive';
        $pelanggan->save();

        // ✅ update Mikrotik
        $mikrotik->updatePPPoESecretStatus(
            $pelanggan->username_pppoe,
            'inactive'
        );
    }

    $this->info('Selesai memproses akun overdue.');
}

}
