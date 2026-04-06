<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PelangganTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'Budi Santoso', 
                'budi_wifi', 
                'pass123', 
                '20 Mbps', 
                'active',
                'budi@gmail.com', 
                '081234567890', 
                '114110064300001', 
                'Jl. Mawar No. 123',
                'https://maps.app.goo.gl/xxxx'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggan',
            'Username PPPoE',
            'Password PPPoE',
            'Nama Paket',
            'Status Akun',
            'Email',
            'No HP',
            'BRIVA',
            'Alamat',
            'Google Maps URL'
        ];
    }
}
