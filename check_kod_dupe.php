<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pelanggans = \App\Models\Pelanggan::all();
$kodeCounts = [];
foreach ($pelanggans as $p) {
    if (!isset($kodeCounts[$p->kode_pelanggan])) {
        $kodeCounts[$p->kode_pelanggan] = 0;
    }
    $kodeCounts[$p->kode_pelanggan]++;
}

foreach ($kodeCounts as $kode => $count) {
    if ($count > 1) {
        echo "DUPLICATE KODE PELANGGAN: {$kode} ($count times)\n";
    }
}
