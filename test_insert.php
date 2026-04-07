<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::table('pelanggans')->insert([
        'kode_pelanggan' => 'TEST',
        'nama_pelanggan' => 'TEST',
        'username_pppoe' => 'TEST_PPPOE',
        'password_pppoe' => 'TEST_PASS',
        'norekening_briva' => '1234567890',
        'status_akun' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Insert Successful!\n";
    DB::table('pelanggans')->where('kode_pelanggan', 'TEST')->delete();
} catch (\Exception $e) {
    echo "Insert Failed: " . $e->getMessage() . "\n";
}
