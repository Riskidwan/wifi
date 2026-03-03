<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Dapatkan paket untuk di-assign ke pelanggan dummy
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

    // 2. Assign id_paket ke pelanggan dummy sebelumnya (agar bisa digenerate tagihannya)
    $updated = \App\Models\Pelanggan::whereNull('id_paket')->update(['id_paket' => $paket->id]);
    if ($updated > 0) {
        echo "Berhasil update " . $updated . " pelanggan sebelumnya agar memiliki Paket Internet.\n";
    }

    // 3. Tambahkan pelanggan sampai jumlah minimal 50 tercapai
    $count = \App\Models\Pelanggan::whereNotNull('id_paket')->count();
    $needed = 55 - $count; // Kita buat 55 supaya pasti di atas 50

    if ($needed > 0) {
        for ($i = 0; $i < $needed; $i++) {
            $lastPelanggan = \App\Models\Pelanggan::orderBy('id_pelanggan', 'desc')->first();
            $nextNumber = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;
            $kodePelanggan = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $nama = "Pelanggan Test " . $kodePelanggan;

            \App\Models\Pelanggan::create([
                'kode_pelanggan' => $kodePelanggan,
                'nama_pelanggan' => $nama,
                'username_pppoe' => 'test_' . strtolower(str_replace(' ', '', $nama)),
                'password_pppoe' => '12345678',
                'email' => strtolower(str_replace(' ', '', $nama)) . '@example.com',
                'no_hp' => '0812345' . rand(10000, 99999),
                'alamat' => 'Jl. Testing Dummy No. ' . rand(1, 100),
                'status_akun' => 'active',
                'id_paket' => $paket->id,
                'foto' => null,
                'google_maps_url' => null,
            ]);
        }
        echo "Berhasil menyiapkan tambahan " . $needed . " pelanggan!\n";
    }

    echo "Total Pelanggan Aktif yang memiliki paket sekarang: " . \App\Models\Pelanggan::whereNotNull('id_paket')->count() . " Pelanggan.\n";

}
catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
