<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;


class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('pelanggan')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan pelanggan
        if ($request->filled('pelanggan')) {
            $query->where('pelanggan_id', $request->pelanggan);
        }

        $invoices = $query->get();
        $pelanggans = Pelanggan::all();

        return view('invoices.index', compact('invoices', 'pelanggans'));
    }

    public function create()
    {
        return view('invoices.create');
    }

    public function store(Request $request)
    {
        // Generate invoice untuk semua pelanggan aktif
        $service = new BillingService();
        $invoices = $service->generateMonthlyInvoices();
        $jumlah_pelanggan = count($invoices);

        Activity::create([
            'log_name' => 'invoice',
            'description' => 'Generate tagihan bulanan untuk ' . $jumlah_pelanggan . ' pelanggan',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()), ]);

        return redirect()->route('invoices.index')
            ->with('success', 'Berhasil generate ' . count($invoices) . ' tagihan bulanan.');
    }

    public function show(Invoice $invoice)
    {
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:unpaid,paid,overdue',
        ]);

        // ✅ Ambil kode pelanggan (lebih singkat)
        $kodePelanggan = $invoice->pelanggan->kode_pelanggan ?? 'N/A';
        $oldStatus = $invoice->status;
        $newStatus = $request->status;

        $invoice->update($request->only('status'));
        // ✅ Log singkat: hanya kode pelanggan
        Activity::create([
            'log_name' => 'invoice',
            'description' => "Mengubah status invoice jadi {$newStatus} untuk pelanggan {$kodePelanggan}",
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return redirect()->route('invoices.index')
            ->with('success', 'Status tagihan berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        Activity::create([
            'log_name' => 'invoice',
            'description' => 'hapus invoice',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);
        return redirect()->route('invoices.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    // Tambahkan method-method ini di dalam class InvoiceController
    public function downloadPdf(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();
        $pdf = Pdf::loadView('invoices.template', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function previewPdf(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();
        $pdf = Pdf::loadView('invoices.template', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function downloadPdfAdvanced(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();
        $pdf = Pdf::loadView('invoices.template-advanced', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice-' . $invoice->invoice_number . '-advanced.pdf');
    }

    public function previewPdfAdvanced(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();
        $pdf = Pdf::loadView('invoices.template-advanced', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('invoice-' . $invoice->invoice_number . '-advanced.pdf');
    }
    public function downloadPdfClean(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();

        $pdf = PDF::loadView('invoices.template-clean', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $invoice->invoice_number . '-clean.pdf');
    }
    public function previewPdfClean(Invoice $invoice)
    {
        $billingConfig = \App\Models\BillingConfig::first();

        $pdf = PDF::loadView('invoices.template-clean', compact('invoice', 'billingConfig'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('invoice-' . $invoice->invoice_number . '-clean.pdf');
    }
    public function exportWa()
    {
        $invoices = Invoice::where('status', 'unpaid')
            ->with('pelanggan')
            ->get();

        $content = "";
        foreach ($invoices as $invoice) {
            if (!$invoice->pelanggan->no_hp)
                continue;

            $waNumber = formatWaNumber($invoice->pelanggan->no_hp);
            $message = getInvoiceWaMessage($invoice);

            $content .= "https://web.whatsapp.com/send?phone={$waNumber}&text=" . urlencode($message) . "\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="wa-links.txt"'
        ]);
    }

}