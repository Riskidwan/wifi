<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; 

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('invoice.pelanggan', 'pelanggan')
            ->latest()
            ->paginate(15);
        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $invoice_id = $request->get('invoice_id');
        if (!$invoice_id) {
            abort(404, 'Invoice ID tidak ditemukan');
        }
        $invoice = Invoice::with('pelanggan')->findOrFail($invoice_id);
        return view('payments.create', compact('invoice'));
    }

public function store(Request $request)
{
    $request->validate([
        'amount_paid' => 'required|numeric|min:1',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'receipt_number' => 'required|string',
        'cashier_name' => 'required|string',
    ]);

    $uangDibayar = $request->uang_dibayar_raw ? (int) str_replace('.', '', $request->uang_dibayar_raw) : null;
    $kembalian = $request->kembalian_raw ? (int) str_replace('.', '', $request->kembalian_raw) : null;

    if ($request->has('is_manual')) {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id_pelanggan',
            'jumlah_bulan' => 'required|integer|min:1|max:12',
            // ✅ TIDAK PERLU AMBIL INVOICE DI SINI
        ]);

        $pelanggan = \App\Models\Pelanggan::findOrFail($request->pelanggan_id);

        if ($pelanggan->status_akun !== 'active') {
            $pelanggan->update(['status_akun' => 'active']);
            $this->updatePPPoESecretStatusOnMikrotik($pelanggan->username_pppoe, 'active');
        }

        // ✅ Ambil invoice unpaid sesuai jumlah_bulan
        $today = now()->startOfMonth();

$invoice = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
    ->where('billing_period_start', $today)
    ->first();
        if (!$invoice) {
    return back()->withErrors(['pelanggan_id' => 'Invoice bulan ini belum dibuat.']);
}

        
       // ✅ Simpan pembayaran untuk setiap invoice
        foreach ($unpaidInvoices as $invoice) {
            $total = $invoice->total_amount ?? $invoice->amount;

            \App\Models\Payment::create([
                'invoice_id' => $invoice->id, // ✅ sekarang $invoice sudah ada
                'pelanggan_id' => $pelanggan->id_pelanggan,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number ?? null,
                'amount_paid' => $total,
                'uang_dibayar' => $uangDibayar,
                'kembalian' => $kembalian,
                'payment_date' => $request->payment_date,
                'notes' => "Pembayaran manual {$request->jumlah_bulan} bulan",
                'status' => 'completed',
                'receipt_number' => $request->receipt_number,
                'cashier_name' => $request->cashier_name,
            ]);

            // Update status invoice
            $invoice->update(['status' => 'paid']);
        }

        Activity::create([
            'log_name' => 'payment',
            'description' => 'Pembayaran manual oleh ' . $pelanggan->nama_pelanggan . ' sebesar Rp ' . number_format($request->amount_paid, 0, ',', '.'),
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);
           // Redirect ke struk pembayaran terakhir
        $lastPayment = \App\Models\Payment::where('pelanggan_id', $pelanggan->id_pelanggan)
            ->orderBy('id', 'desc')
            ->first();

        
        return redirect()->route('payments.receipt', $lastPayment->id)
            ->with('success', "Berhasil bayar {$request->jumlah_bulan} bulan untuk {$pelanggan->nama_pelanggan}!");
    }
}

    // Method MikroTik (enable/disable)
    private function updatePPPoESecretStatusOnMikrotik($username, $status)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user) return;

        $API = new \App\Models\RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);
            if (!empty($secrets)) {
                $API->comm('/ppp/secret/set', [
                    '.id' => $secrets[0]['.id'],
                    'disabled' => $status === 'inactive' ? 'yes' : 'no'
                ]);
                
                // Putuskan koneksi jika inactive
                if ($status === 'inactive') {
                    $active = $API->comm('/ppp/active/print', ['?name' => $username]);
                    foreach ($active as $a) {
                        $API->comm('/ppp/active/remove', ['.id' => $a['.id']]);
                    }
                }
            }
            $API->disconnect();
        }
    }

    public function show(Payment $payment)
    {
        $payment->load('invoice.pelanggan', 'pelanggan');
        return view('payments.show', compact('payment'));
    }

   public function createManual()
{
    $pelanggans = \App\Models\Pelanggan::whereNotNull('id_paket') // cukup pastikan punya paket
        ->with(['paket', 'invoices' => function ($q) {
            $q->where('status', 'unpaid');
        }])
        ->get(); // ✅ jangan filter status_akun

    return view('payments.create-manual', compact('pelanggans'));
}
 public function receipt(Payment $payment)
{
    // Pastikan relasi dimuat
    $payment->load('pelanggan', 'invoice');

    $billingConfig = \App\Models\BillingConfig::first();

    return view('payments.receipt', compact('payment', 'billingConfig'));
}
}