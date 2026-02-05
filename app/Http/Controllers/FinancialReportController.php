<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;
use App\Models\PemasukanManual;
use Spatie\Activitylog\Models\Activity; 
use App\Models\KategoriKeuangan;
use Illuminate\Validation\Rule; 

class FinancialReportController extends Controller
{
public function indexPemasukan(Request $request)
{
    $pemasukan = PemasukanManual::latest()->paginate(10);

     $kategori_pemasukan = KategoriKeuangan::where('tipe', 'pemasukan')->get();
    return view('laporan.pemasukan', compact('pemasukan', 'kategori_pemasukan'))
        ->with('menu', 'laporan')
        ->with('submenu', 'pemasukan');
}

public function indexPengeluaran(Request $request)
{
   $expenses = Expense::latest()->paginate(10);
    
    // ✅ Ambil kategori pengeluaran
    $kategori_pengeluaran = KategoriKeuangan::where('tipe', 'pengeluaran')->get();

    // ✅ Kirim ke view
    return view('laporan.pengeluaran', compact('expenses', 'kategori_pengeluaran'))
        ->with('menu', 'laporan')
        ->with('submenu', 'pengeluaran');
}
    public function index(Request $request)
{
    // Ambil tanggal dari request, default: bulan ini
    $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

    // Validasi tanggal
    if (!\Carbon\Carbon::parse($startDate)->isValid() || !\Carbon\Carbon::parse($endDate)->isValid()) {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
    }

    // Pastikan start <= end
    if (\Carbon\Carbon::parse($startDate)->gt(\Carbon\Carbon::parse($endDate))) {
        $temp = $startDate;
        $startDate = $endDate;
        $endDate = $temp;
    }

    // 📈 Pemasukan (Pembayaran Pelanggan)
    $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
        ->orderBy('payment_date', 'desc')
        ->paginate(10);

    $totalPayments = $payments->sum('amount_paid');

    // 💰 Pemasukan Manual
    $manualIncome = PemasukanManual::whereBetween('tanggal', [$startDate, $endDate])
        ->orderBy('tanggal', 'desc')
        ->paginate(10);

    $totalManualIncome = $manualIncome->sum('jumlah');

    // 📊 Total Pemasukan
    $income = $totalPayments + $totalManualIncome;

    // 💸 Piutang (Tagihan Belum Bayar)
    $unpaidInvoices = Invoice::where('status', 'unpaid')
        ->whereBetween('due_date', [$startDate, $endDate])
        ->sum('amount');

    // 💰 Pengeluaran
    $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
        ->get()
        ->groupBy('category')
        ->map(function ($items) {
            return $items->sum('amount');
        });

    $totalExpenses = $expenses->sum();
    $netProfit = $income - $totalExpenses;

    // 📋 Daftar Pengeluaran Detail
    $expenseList = Expense::whereBetween('expense_date', [$startDate, $endDate])
        ->orderBy('expense_date', 'desc')
        ->paginate(10);

    // 📊 Gabungkan Pemasukan
    $incomeList = collect();
    foreach ($payments as $payment) {
        $incomeList->push([
            'type' => 'payment',
            'date' => $payment->payment_date,
            'customer' => $payment->pelanggan->nama_pelanggan ?? 'N/A',
            'invoice' => $payment->invoice->invoice_number ?? 'N/A',
            'amount' => $payment->amount_paid,
            'source' => 'Pelanggan'
        ]);
    }
    foreach ($manualIncome as $m) {
        $incomeList->push([
            'type' => 'manual',
            'date' => $m->tanggal,
            'customer' => null,
            'invoice' => null,
            'amount' => $m->jumlah,
            'source' => 'Manual: ' . $m->kategori
        ]);
    }
    $incomeList = $incomeList->sortByDesc('date')->values();

    return view('laporan.keuangan', compact(
        'startDate', 'endDate', 'income', 'unpaidInvoices', 
        'expenses', 'totalExpenses', 'netProfit', 
        'expenseList', 'incomeList', 'payments', 'manualIncome'
    ));
}

    public function store(Request $request)
    {

        $request->validate([
           'category' => 'required|string|max:100', 
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date'
        ]);

        Expense::create([
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            // 'receipt_number' => $request->receipt_number
        ]);
        // Di store() → pengeluaran
Activity::create([
    'log_name' => 'expense',
    'description' => 'pengeluaran: ' . $request->keterangan . ' (Rp ' . number_format($request->amount,0,',','.') . ')',
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        return Excel::download(new FinancialReportExport($startDate, $endDate), 'laporan-keuangan-' . date('Ymd') . '.xlsx');
    }

    public function storePemasukanManual(Request $request)
{
    $request->validate([
        'kategori' => 'required|string|max:255',
        'jumlah' => 'required|numeric|min:0',
        'keterangan' => 'nullable|string',
        'tanggal' => 'required|date',
    ]);

    PemasukanManual::create([
        'kategori' => $request->kategori,
        'keterangan' => $request->keterangan,
        'jumlah' => $request->jumlah,
        'tanggal' => $request->tanggal,
    ]);
     Activity::create([
        'log_name' => 'income',
        'description' => 'pemasukan manual: ' . ($request->keterangan ?: $request->kategori) . ' (Rp ' . number_format($request->jumlah, 0, ',', '.') . ')',
        'causer_id' => auth()->id(),
        'causer_type' => get_class(auth()->user()),
    ]);

    return redirect()->back()->with('success', 'Pemasukan manual berhasil ditambahkan!');
}
// ===== PEMASUKAN MANUAL =====

    public function editPemasukan($id)
    {
        $pemasukan = PemasukanManual::findOrFail($id);
    $kategori_pemasukan = KategoriKeuangan::where('tipe', 'pemasukan')->get();
    
    // ✅ Kirim $kategori_pemasukan
    return view('laporan.pemasukan_edit', compact('pemasukan', 'kategori_pemasukan'));
    }

    public function updatePemasukan(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date',
        ]);

        $pemasukan = PemasukanManual::findOrFail($id);
        $pemasukan->update($request->only(['kategori', 'jumlah', 'keterangan', 'tanggal']));

        Activity::create([
            'log_name' => 'income',
            'description' => 'Mengupdate pemasukan manual: ' . $pemasukan->kategori,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return redirect()->route('pemasukan.manual.index')->with('success', 'Pemasukan berhasil diperbarui!');
    }

    public function destroyPemasukan($id)
    {
        $pemasukan = PemasukanManual::findOrFail($id);
        Activity::create([
            'log_name' => 'income',
            'description' => 'Menghapus pemasukan manual: ' . $pemasukan->kategori,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);
        $pemasukan->delete();
        return redirect()->route('pemasukan.manual.index')->with('success', 'Pemasukan berhasil dihapus!');
    }

    // ===== PENGELUARAN =====


    public function editPengeluaran($id)
    {
        $expense = Expense::findOrFail($id);
    $kategori_pengeluaran = KategoriKeuangan::where('tipe', 'pengeluaran')->get();
    
    return view('laporan.pengeluaran_edit', compact('expense', 'kategori_pengeluaran'));
    }

    public function updatePengeluaran(Request $request, $id)
    {
      
        $request->validate([
          'category' => 'required|string|max:100',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date'
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update($request->all());

        Activity::create([
            'log_name' => 'expense',
            'description' => 'Mengupdate pengeluaran: ' . $expense->description,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function destroyPengeluaran($id)
    {
        $expense = Expense::findOrFail($id);
        Activity::create([
            'log_name' => 'expense',
            'description' => 'Menghapus pengeluaran: ' . $expense->description,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);
        $expense->delete();
        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dihapus!');
    }
}