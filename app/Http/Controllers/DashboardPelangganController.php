<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;   // ✅
use App\Models\Payment;     // ✅
use App\Models\Expense;     // ✅
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class DashboardPelangganController extends Controller
{
    public function index()
    {
 $menu = 'dashboard-pelanggan';
    $submenu = ''; // ← tambahkan ini

        // 📊 Total Pelanggan
        $totalPelanggan = Pelanggan::count();
        $pelangganAktif = Pelanggan::where('status_akun', 'active')->count();
        $pelangganTidakAktif = Pelanggan::where('status_akun', 'inactive')->count();

        // 💰 Pemasukan Bulan Ini
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $pendapatan = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount_paid');

        // 💸 Pengeluaran Bulan Ini
        $pengeluaran = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // 📈 Saldo Bersih
        $saldoBersih = $pendapatan - $pengeluaran;

         $activities = Activity::latest()->take(4)->get(); // ← hanya 5 baris

        return view('dashboard.pelanggan', compact(
                   'menu', 'submenu', // ← pastikan ini ada
            'totalPelanggan', 'pelangganAktif', 'pelangganTidakAktif',
            'pendapatan', 'pengeluaran', 'saldoBersih',
              'activities' // ← TAMBAHKAN INI
        ));
    }
}