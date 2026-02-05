<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
    'invoice_id',
    'pelanggan_id',
    'payment_method',
    'reference_number',
    'amount_paid',
    'payment_date',
    'notes',
    'status',
    'receipt_number',   // ← Tambahkan ini
    'cashier_name',     // ← Tambahkan ini
    'payment_proof' ,    // ← Tambahkan ini
        'uang_dibayar',
    'kembalian',
];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id', 'id_pelanggan');
    }
}