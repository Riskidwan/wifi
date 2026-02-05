<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Nonaktifkan pelanggan yang lewat jatuh tempo';

    public function handle()
    {
        $today = now()->startOfDay();
        $pelanggans = Pelanggan::where('status_akun', 'active')->get();

        foreach ($pelanggans as $pelanggan) {
            // Cek apakah sudah bayar untuk bulan ini
            $paidThisMonth = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                ->where('billing_period_start', '<=', now()->endOfMonth())
                ->where('billing_period_end', '>=', now()->startOfMonth())
                ->where('status', 'paid')
                ->exists();

            if (!$paidThisMonth) {
                // Cari invoice terakhir yang unpaid
                $lastUnpaid = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                    ->where('status', 'unpaid')
                    ->orderBy('due_date', 'desc')
                    ->first();

                if ($lastUnpaid && $lastUnpaid->due_date < $today) {
                    // Nonaktifkan pelanggan
                    $pelanggan->update(['status_akun' => 'inactive']);
                    
                    // Nonaktifkan di MikroTik
                    $this->updatePPPoESecretStatusOnMikrotik($pelanggan->username_pppoe, 'inactive');
                    
                    $this->info("Nonaktifkan: {$pelanggan->nama_pelanggan} (overdue)");
                }
            }
        }
    }

    private function updatePPPoESecretStatusOnMikrotik($username, $status)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user) return;

        $API = new \App\Models\RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);
            if (!empty($secrets)) {
                $API->comm('/ppp/secret/set', [
                    '.id' => $secrets[0]['.id'],
                    'disabled' => $status === 'inactive' ? 'yes' : 'no'
                ]);
                if ($status === 'inactive') {
                    $active = $API->comm('/ppp/active/print', ['?name' => $username]);
                    foreach ($active as $a) {
                        $API->comm('/ppp/active/remove', ['.id' => $a['.id']]);
                    }
                }
            }
            $API->disconnect();
        }
    }
}