<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'receipt_number'
    ];

    // Helper untuk nama kategori
    public function getCategoryNameAttribute()
    {
        $categories = [
            'bandwidth' => 'Bandwidth',
            'equipment' => 'Alat/Perangkat',
            'salary' => 'Gaji',
            'maintenance' => 'Perawatan',
            'other' => 'Lain-lain'
        ];
        return $categories[$this->category] ?? $this->category;
    }
}