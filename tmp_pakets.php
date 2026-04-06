<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pakets = App\Models\Paket::all(['id', 'nama_paket', 'harga'])->toArray();
echo json_encode($pakets);
