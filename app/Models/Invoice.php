<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $table = 'invoices';    protected $primaryKey = 'id';

    protected $fillable = [
        'invoice_number',
        'pelanggan_id',
        'paket_nama',
        'amount',
        'total_amount',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'status'
    ];    public function pelanggan()    {
        return $this->belongsTo(\App\Models\Pelanggan::class , 'pelanggan_id', 'id_pelanggan');    }
    public function paket()
    {
        // Karena tidak ada id_paket, kita cari paket berdasarkan nama
        return \App\Models\Paket::where('nama_paket', $this->paket_nama)->first();
    }

}