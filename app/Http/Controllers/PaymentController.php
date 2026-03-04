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
        $pelanggan = $invoice->pelanggan;
        $paymentCount = \App\Models\Payment::where('pelanggan_id', $pelanggan->id_pelanggan)->count() + 1;
        $receiptNumber = $pelanggan->kode_pelanggan . str_pad($paymentCount, 2, '0', STR_PAD_LEFT) . date('dmy');

        return view('payments.create', compact('invoice', 'receiptNumber'));
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
            $unpaidInvoices = Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                ->where('status', 'unpaid')
                ->orderBy('billing_period_start', 'asc')
                ->limit($request->jumlah_bulan)
                ->get();

            if ($unpaidInvoices->isEmpty()) {
                return back()->withErrors(['pelanggan_id' => 'Tidak ada invoice yang belum lunas.']);
            }
            // ✅ Simpan pembayaran untuk setiap invoice
            foreach ($unpaidInvoices as $invoice) {
                $total = $invoice->total_amount ?? $invoice->amount;

                $paymentCount = \App\Models\Payment::where('pelanggan_id', $pelanggan->id_pelanggan)->count() + 1;
                $receiptNumber = $pelanggan->kode_pelanggan . str_pad($paymentCount, 2, '0', STR_PAD_LEFT) . date('dmy');

                \App\Models\Payment::create([
                    'invoice_id' => $invoice->id,
                    'pelanggan_id' => $pelanggan->id_pelanggan,
                    'payment_method' => $request->payment_method,
                    'reference_number' => $request->reference_number ?? null,
                    'amount_paid' => $total,
                    'uang_dibayar' => $uangDibayar,
                    'kembalian' => $kembalian,
                    'payment_date' => $request->payment_date,
                    'notes' => "Pembayaran manual {$request->jumlah_bulan} bulan",
                    'status' => 'completed',
                    'receipt_number' => $receiptNumber,
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
        } else {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $pelanggan = $invoice->pelanggan;

            if ($pelanggan->status_akun !== 'active') {
                $pelanggan->update(['status_akun' => 'active']);
                $this->updatePPPoESecretStatusOnMikrotik($pelanggan->username_pppoe, 'active');
            }

            $paymentCount = \App\Models\Payment::where('pelanggan_id', $pelanggan->id_pelanggan)->count() + 1;
            $receiptNumber = $pelanggan->kode_pelanggan . str_pad($paymentCount, 2, '0', STR_PAD_LEFT) . date('dmy');

            $payment = \App\Models\Payment::create([
                'invoice_id' => $invoice->id,
                'pelanggan_id' => $pelanggan->id_pelanggan,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number ?? null,
                'amount_paid' => $request->amount_paid,
                'uang_dibayar' => $uangDibayar,
                'kembalian' => $kembalian,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes ?? "Pembayaran Tagihan {$invoice->invoice_number}",
                'status' => 'completed',
                'receipt_number' => $receiptNumber,
                'cashier_name' => $request->cashier_name,
            ]);

            $invoice->update(['status' => 'paid']);

            Activity::create([
                'log_name' => 'payment',
                'description' => 'Pembayaran tagihan oleh ' . $pelanggan->nama_pelanggan . ' sebesar Rp ' . number_format($request->amount_paid, 0, ',', '.'),
                'causer_id' => auth()->id(),
                'causer_type' => get_class(auth()->user()),
            ]);

            return redirect()->route('payments.receipt', $payment->id)
                ->with('success', "Berhasil menerima pembayaran dari {$pelanggan->nama_pelanggan}!");
        }
    }

    // Method MikroTik (enable/disable)
    private function updatePPPoESecretStatusOnMikrotik($username, $status)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user)
            return;

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
            ->with([
                'paket',
                'invoices' => function ($q) {
                    $q->where('status', 'unpaid');
                }
            ])
            ->get(); // ✅ jangan filter status_akun

        $nextReceipts = [];
        $dateStr = date('dmy');
        foreach ($pelanggans as $p) {
            $count = \App\Models\Payment::where('pelanggan_id', $p->id_pelanggan)->count() + 1;
            $nextReceipts[$p->id_pelanggan] = $p->kode_pelanggan . str_pad($count, 2, '0', STR_PAD_LEFT) . $dateStr;
        }

        return view('payments.create-manual', compact('pelanggans', 'nextReceipts'));
    }
    public function receipt(Payment $payment)
    {
        // Pastikan relasi dimuat
        $payment->load('pelanggan', 'invoice');

        $billingConfig = \App\Models\BillingConfig::first();

        return view('payments.receipt', compact('payment', 'billingConfig'));
    }
}