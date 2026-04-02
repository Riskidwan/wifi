<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pelanggans = \App\Models\Pelanggan::orderBy('id_pelanggan')->get();
$counter = 1;

foreach ($pelanggans as $p) {
    $kodePelanggan = str_pad($counter, 4, '0', STR_PAD_LEFT);
    $p->kode_pelanggan = $kodePelanggan;
    $p->save();
    $counter++;
}

echo "Berhasil mengatur ulang kode_pelanggan agar unik dan berurutan dari 0001 sampai " . str_pad($counter - 1, 4, '0', STR_PAD_LEFT) . "!\n";
