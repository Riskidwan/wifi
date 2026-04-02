<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billingConfig = \App\Models\BillingConfig::first();
$dueDays = $billingConfig->due_days_after_period ?? 5;

$pelanggans = \App\Models\Pelanggan::where('status_akun', 'active')
    ->with('paket')
    ->get();

$today = \Carbon\Carbon::today();

// Hanya generate untuk BULAN INI
$periodStart = $today->copy()->startOfMonth();
$periodEnd = $today->copy()->endOfMonth();
$dueDate = $periodStart->copy()->addDays($dueDays - 1);

foreach ($pelanggans as $pelanggan) {
    if (!$pelanggan->paket) {
        echo "Skip {$pelanggan->id_pelanggan}: no paket\n";
        continue;
    }

    $existing = \App\Models\Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
        ->where('billing_period_start', $periodStart)
        ->first();

    if ($existing) {
        // echo "Skip {$pelanggan->id_pelanggan}: existing invoice\n";
        continue; // Sudah ada → skip
    }

    // Ambil paket
    $paket = \App\Models\Paket::where('nama_paket', $pelanggan->paket->nama_paket)->first();
    if (!$paket) {
        echo "Skip {$pelanggan->id_pelanggan}: paket nama {$pelanggan->paket->nama_paket} not found in DB\n";
        continue;
    }

    echo "READY TO GENERATE: {$pelanggan->id_pelanggan} (Paket: {$paket->nama_paket})\n";
}
