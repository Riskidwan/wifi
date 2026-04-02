<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pelanggan extends Model
{
    use LogsActivity;
    use HasFactory;
    protected $table = 'pelanggans'; 
   protected $primaryKey = 'id_pelanggan';
public $incrementing = true;
protected $keyType = 'int';
    // app/Models/Pelanggan.php
protected $fillable = [
    'kode_pelanggan',
    'nama_pelanggan',
    'username_pppoe',
    'password_pppoe',
    'email',
    'no_hp',
    'norekening_briva',
    'alamat',
    'status_akun',
    'id_paket',
    'foto', // ← tambahkan ini
    'google_maps_url',
];
// Tentukan field yang dilog
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_pelanggan', 'status_akun', 'username_pppoe'])
            ->logOnlyDirty() // hanya log perubahan
            ->setDescriptionForEvent(fn(string $eventName) => "Pelanggan {$this->nama_pelanggan} {$eventName}");
    }

protected $casts = [
    'foto' => 'array', // otomatis serialize/unserialize JSON
];

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }

    public function billing()
    {
        return $this->hasMany(Billing::class, 'id_pelanggan');
    }
    public function invoices()
{
    return $this->hasMany(\App\Models\Invoice::class, 'pelanggan_id', 'id_pelanggan');
}
    
}