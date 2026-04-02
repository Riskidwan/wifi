<?php

namespace App\Http\Controllers;

use App\Models\BillingConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    public function saveToken(Request $request)
    {
        $request->validate(['wa_token' => 'required|string|min:5']);
        $config = BillingConfig::first();
        if (!$config) return response()->json(['success' => false, 'message' => 'Config belum ada.'], 400);
        $config->update(['wa_token' => $request->wa_token]);
        return response()->json(['success' => true, 'message' => 'Token berhasil disimpan!']);
    }

    public function getQR()
    {
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'message' => 'Token belum diatur.'], 400);
        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/qr');
            $data = $response->json();
            if (isset($data['url'])) return response()->json(['success' => true, 'qr_url' => $data['url']]);
            if (isset($data['status']) && $data['status'] === true) return response()->json(['success' => true, 'connected' => true, 'message' => $data['reason'] ?? 'Device sudah terhubung.']);
            return response()->json(['success' => false, 'message' => $data['reason'] ?? $data['detail'] ?? 'Gagal mengambil QR.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function status()
    {
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'connected' => false, 'message' => 'Token belum diatur.']);
        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/device');
            $data = $response->json();
            $connected = isset($data['status']) && $data['status'] === true;
            return response()->json([
                'success' => true,
                'connected' => $connected,
                'device' => $data['device'] ?? null,
                'message' => $connected ? 'WhatsApp terhubung.' : ($data['reason'] ?? 'WhatsApp tidak terhubung.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'connected' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function disconnect()
    {
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'message' => 'Token belum diatur.'], 400);
        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/disconnect');
            $data = $response->json();
            return response()->json(['success' => true, 'message' => $data['detail'] ?? 'Device disconnected.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function test(Request $request)
    {
        $request->validate(['phone' => 'required|string']);
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'message' => 'Token belum diatur.'], 400);
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => "TEST WA GATEWAY\nPesan dari Sistem Billing.\nWaktu: " . now()->format('d M Y H:i:s'),
                'countryCode' => '62',
            ]);
            $data = $response->json();
            $sent = isset($data['status']) && $data['status'] === true;
            return response()->json([
                'success' => $sent,
                'message' => $sent ? 'Pesan test berhasil dikirim!' : ($data['reason'] ?? $data['detail'] ?? 'Gagal kirim.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function sendInvoice(Request $request)
    {
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'message' => 'Token WhatsApp belum diatur. Konfigurasi di Setting.'], 400);
        $invoice = \App\Models\Invoice::with('pelanggan')->findOrFail($request->invoice_id);
        if (!$invoice->pelanggan || !$invoice->pelanggan->no_hp) {
            return response()->json(['success' => false, 'message' => 'Nomor HP pelanggan tidak tersedia.'], 400);
        }
        $phone = formatWaNumber($invoice->pelanggan->no_hp);
        $message = getInvoiceWaMessage($invoice);
        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);
            $data = $response->json();
            $sent = isset($data['status']) && $data['status'] === true;
            return response()->json([
                'success' => $sent,
                'message' => $sent ? 'Tagihan berhasil dikirim ke ' . $invoice->pelanggan->nama_pelanggan : ($data['reason'] ?? $data['detail'] ?? 'Gagal mengirim.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function sendBulkInvoice(Request $request)
    {
        $token = $this->getToken();
        if (!$token) return response()->json(['success' => false, 'message' => 'Token WhatsApp belum diatur.'], 400);
        $invoiceIds = $request->invoice_ids ?? [];
        if (empty($invoiceIds)) return response()->json(['success' => false, 'message' => 'Tidak ada invoice untuk dikirim.'], 400);
        $invoices = \App\Models\Invoice::with('pelanggan')->whereIn('id', $invoiceIds)->get();
        $sent = 0;
        $failed = 0;
        $errors = [];
        foreach ($invoices as $invoice) {
            if (!$invoice->pelanggan || !$invoice->pelanggan->no_hp) {
                $failed++;
                $errors[] = $invoice->invoice_number . ' - tidak ada nomor HP';
                continue;
            }
            $phone = formatWaNumber($invoice->pelanggan->no_hp);
            $message = getInvoiceWaMessage($invoice);
            try {
                $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === true) {
                    $sent++;
                } else {
                    $failed++;
                    $errors[] = $invoice->invoice_number . ' - ' . ($data['reason'] ?? 'gagal');
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $invoice->invoice_number . ' - ' . $e->getMessage();
            }
            usleep(1000000);
        }
        return response()->json([
            'success' => $sent > 0,
            'message' => "Terkirim: {$sent}, Gagal: {$failed}",
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    private function getToken(): ?string
    {
        $config = BillingConfig::first();
        return $config?->wa_token;
    }
}