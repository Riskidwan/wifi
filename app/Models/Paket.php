<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;

    // Izinkan kolom ini diisi via create() atau update()
    protected $fillable = [
        'nama_paket',
        'kecepatan',
        'harga',
        'keterangan',
        'local_address',
        'remote_address',
        'diskon_persen',
        'diskon_aktif',
        'ppn_aktif',
        'ppn_persen',
    ];
    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class, 'id_paket');
    }
}