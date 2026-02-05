<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemasukanManual extends Model
{
    protected $table = 'pemasukan_manual';
    protected $fillable = ['kategori', 'jumlah', 'keterangan', 'tanggal'];
}