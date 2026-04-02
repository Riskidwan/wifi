<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class FinancialReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Gabungkan data pemasukan & pengeluaran
        $payments = Payment::with('pelanggan', 'invoice')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'tipe' => 'Pemasukan',
                    'tanggal' => $item->payment_date,
                    'deskripsi' => "Pembayaran {$item->pelanggan->nama_pelanggan} (Invoice: {$item->invoice->invoice_number})",
                    'jumlah' => $item->amount_paid,
                    'kategori' => '-'
                ];
            });

        $expenses = Expense::whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'tipe' => 'Pengeluaran',
                    'tanggal' => $item->expense_date,
                    'deskripsi' => $item->description,
                    'jumlah' => $item->amount,
                    'kategori' => $item->category_name
                ];
            });

        return $payments->merge($expenses);
    }

    public function headings(): array
    {
        return [
            'Tipe',
            'Tanggal',
            'Deskripsi',
            'Jumlah',
            'Kategori'
        ];
    }

    public function map($row): array
    {
        return [
            $row['tipe'],
            $row['tanggal'],
            $row['deskripsi'],
            $row['jumlah'],
            $row['kategori']
        ];
    }
}