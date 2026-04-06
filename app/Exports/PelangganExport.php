<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PelangganExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Pelanggan::with('paket')->get();
    }

    public function headings(): array
    {
        return [
            'ID Pelanggan',
            'Kode Pelanggan',
            'Nama Pelanggan',
            'Username PPPoE',
            'Password PPPoE',
            'Paket Internet',
            'Email',
            'No HP',
            'BRIVA',
            'Alamat',
            'Status Akun',
            'Created At'
        ];
    }

    public function map($p): array
    {
        return [
            $p->id_pelanggan,
            $p->kode_pelanggan,
            $p->nama_pelanggan,
            $p->username_pppoe,
            $p->password_pppoe,
            $p->paket ? $p->paket->nama_paket : '-',
            $p->email,
            $p->no_hp,
            $p->norekening_briva,
            $p->alamat,
            $p->status_akun,
            $p->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
