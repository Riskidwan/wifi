<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            // ✅ Simpan pembayaran untuk setiap invoice dalam transaksi
            DB::transaction(function () use ($unpaidInvoices, $request, $pelanggan, $uangDibayar, $kembalian) {
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
            });

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

            $payment = DB::transaction(function () use ($invoice, $pelanggan, $request, $uangDibayar, $kembalian) {
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

                return $payment;
            });

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

    /**
     * 📶 Response JSON untuk Bluetooth Print App (Android)
     * Format: JSON_FORCE_OBJECT sesuai dokumentasi mate.bluetoothprint
     * Printer: Thermal 58mm / 48mm area cetak / 32 karakter per baris
     */
    public function receiptJson(Payment $payment)
    {
        \Carbon\Carbon::setLocale('id');
        $payment->load('pelanggan.paket', 'invoice');
        $billingConfig = \App\Models\BillingConfig::first();

        $W = 32;
        $divider = str_repeat('-', $W);

        // Helper baris kiri-kanan
        $lrLine = function ($left, $right) use ($W) {
            $space = max(1, $W - mb_strlen($left) - mb_strlen($right));
            return $left . str_repeat(' ', $space) . $right;
        };

        // Data perusahaan
        $companyName = $billingConfig->company_name ?? 'PEMALANG';
        $companyAddr = $billingConfig->company_address ?? '';
        $companyPhone = $billingConfig->company_phone ?? '0895800439251';

        // Data paket
        $paket = $payment->pelanggan->paket;
        if ($paket) {
            $hargaDasar = $paket->harga;
            $diskonPersen = $paket->diskon_aktif ? ($paket->diskon_persen ?? 0) : 0;
            $ppnPersen = $paket->ppn_aktif ? ($paket->ppn_persen ?? 11) : 0;
            $diskon = $hargaDasar * ($diskonPersen / 100);
            $ppn = $hargaDasar * ($ppnPersen / 100);
            $total = $hargaDasar + $ppn - $diskon;
            $namaPaket = $paket->nama_paket;
        } else {
            $hargaDasar = $payment->amount_paid;
            $diskonPersen = 0;
            $ppnPersen = 0;
            $diskon = 0;
            $ppn = 0;
            $total = $payment->amount_paid;
            $namaPaket = '-';
        }

        $fHarga = 'Rp.' . number_format($hargaDasar, 0, ',', '.');
        $fPpn = 'Rp.' . number_format($ppn, 0, ',', '.');
        $fDiskon = 'Rp.' . number_format($diskon, 0, ',', '.');
        $fTotal = 'Rp ' . number_format($total, 0, ',', '.');
        $fUang = $payment->uang_dibayar ? 'Rp ' . number_format($payment->uang_dibayar, 0, ',', '.') : '';
        $fKembal = $payment->kembalian !== null ? 'Rp ' . number_format($payment->kembalian, 0, ',', '.') : '';

        $periode = now()->translatedFormat('F Y');
        $tanggal = now()->translatedFormat('d F Y H:i');

        // ===== Bangun array JSON =====
        $a = [];

        // -- Header Toko --
        $a[] = (object) ['type' => 0, 'content' => $companyName, 'bold' => 1, 'align' => 1, 'format' => 2];
        if ($companyAddr) {
            $a[] = (object) ['type' => 0, 'content' => $companyAddr, 'bold' => 0, 'align' => 1, 'format' => 0];
        }
        $a[] = (object) ['type' => 0, 'content' => 'CS: ' . $companyPhone, 'bold' => 0, 'align' => 1, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];

        // -- Judul --
        $a[] = (object) ['type' => 0, 'content' => 'BUKTI PEMBAYARAN', 'bold' => 1, 'align' => 1, 'format' => 2];
        $a[] = (object) ['type' => 0, 'content' => 'No. Struk: ' . $payment->receipt_number, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => 'Tanggal  : ' . $tanggal, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];

        // -- Info Pelanggan --
        $a[] = (object) ['type' => 0, 'content' => 'ID PEL: ' . $payment->pelanggan->kode_pelanggan, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => 'Nama  : ' . $payment->pelanggan->nama_pelanggan, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];

        // -- Periode --
        $a[] = (object) ['type' => 0, 'content' => 'Periode: ' . $periode, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];

        // -- Detail Paket --
        $a[] = (object) ['type' => 0, 'content' => $lrLine('Paket', $namaPaket), 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $lrLine('Harga', $fHarga), 'bold' => 0, 'align' => 0, 'format' => 0];
        if ($ppnPersen > 0) {
            $a[] = (object) ['type' => 0, 'content' => $lrLine('PPN ' . $ppnPersen . '%', $fPpn), 'bold' => 0, 'align' => 0, 'format' => 0];
        }
        if ($diskonPersen > 0) {
            $a[] = (object) ['type' => 0, 'content' => $lrLine('Diskon ' . $diskonPersen . '%', $fDiskon), 'bold' => 0, 'align' => 0, 'format' => 0];
        }
        $a[] = (object) ['type' => 0, 'content' => $lrLine('TOTAL', $fTotal), 'bold' => 1, 'align' => 0, 'format' => 3];

        // -- Uang & Kembalian --
        if ($payment->uang_dibayar) {
            $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];
            $a[] = (object) ['type' => 0, 'content' => $lrLine('Uang Dibayar', $fUang), 'bold' => 0, 'align' => 0, 'format' => 0];
            $a[] = (object) ['type' => 0, 'content' => $lrLine('Kembalian', $fKembal), 'bold' => 0, 'align' => 0, 'format' => 0];
        }

        // -- Metode & Kasir --
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => 'Metode: ' . ucfirst($payment->payment_method), 'bold' => 0, 'align' => 0, 'format' => 0];
        if ($payment->reference_number) {
            $a[] = (object) ['type' => 0, 'content' => 'Ref   : ' . $payment->reference_number, 'bold' => 0, 'align' => 0, 'format' => 0];
        }
        $a[] = (object) ['type' => 0, 'content' => 'Kasir : ' . $payment->cashier_name, 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => $divider, 'bold' => 0, 'align' => 0, 'format' => 0];

        // -- Footer --
        $a[] = (object) ['type' => 0, 'content' => 'Terima kasih', 'bold' => 0, 'align' => 1, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => ' ', 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => ' ', 'bold' => 0, 'align' => 0, 'format' => 0];
        $a[] = (object) ['type' => 0, 'content' => ' ', 'bold' => 0, 'align' => 0, 'format' => 0];

        return response()->json($a, 200, [], JSON_FORCE_OBJECT);
    }
}