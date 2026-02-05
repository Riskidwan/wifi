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
          'diskon_persen',
    'ppn_aktif',
    'ppn_persen',
    ];
}