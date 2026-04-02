<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Dapatkan paket yang valid
    $paket = \App\Models\Paket::first();
    if (!$paket) {
        $paket = \App\Models\Paket::create([
            'nama_paket' => 'Paket Dummy 10Mbps',
            'harga' => 150000,
            'keterangan' => 'Dummy untuk testing tagihan',
            'kecepatan' => '10M/10M',
            'diskon_aktif' => false,
            'ppn_aktif' => false,
        ]);
        echo "Paket internet dibuat khusus simulasi.\n";
    }

    $validPaketIds = \App\Models\Paket::pluck('id')->toArray();

    // 2. Ambil pelanggan yang id_paket-nya tidak ada di pakets table
    $orphanedCount = \App\Models\Pelanggan::whereNotIn('id_paket', $validPaketIds)
        ->orWhereNull('id_paket')
        ->update(['id_paket' => $paket->id]);

    echo "Berhasil update " . $orphanedCount . " pelanggan yatim (orphaned) agar memiliki Paket Internet yang valid ({$paket->nama_paket}).\n";

}
catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
