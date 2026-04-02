<?php

namespace App\Http\Controllers;

use App\Models\MikrotikSetting;
use App\Models\BillingConfig;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\RouterosAPI;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil kredensial MikroTik dari session
        $mikrotik = [
            'ip' => $request->session()->get('ip', '192.168.88.1'),
            'user' => $request->session()->get('user', 'admin'),
            'password' => $request->session()->get('password', ''),
        ];

        $billing = BillingConfig::first();
        $setting = MikrotikSetting::first();

        return view('setting.index', compact('mikrotik', 'billing', 'setting'));
    }

    public function update(Request $request)
    {
        $section = $request->input('section');

        switch ($section) {
            case 'mikrotik':
                return $this->updateMikrotik($request);
            case 'billing':
                return $this->updateBilling($request);
            case 'whatsapp':
                return $this->updateWhatsapp($request);
            default:
                return back()->with('error', 'Seksi tidak valid.');
        }
    }

    private function updateMikrotik(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'user' => 'required|string',
            'password' => 'nullable|string',
        ]);

        // Cek Koneksi MikroTik
        $mikrotikWarning = null;
        try {
            $API = new RouterosAPI();
            $connected = $API->connect($request->ip, $request->user, $request->password);

            if (!$connected) {
                $mikrotikWarning = '⚠️ Konfigurasi tersimpan, TETAPI koneksi ke MikroTik Gagal.';
            } else {
                $API->disconnect();
            }
        } catch (\Exception $e) {
            $mikrotikWarning = '⚠️ Konfigurasi tersimpan, TETAPI tidak bisa terhubung ke MikroTik.';
        }

        $mikrotik = MikrotikSetting::first();
        $data = ['ip' => $request->ip, 'username' => $request->user, 'password' => $request->password];

        if (!$mikrotik) MikrotikSetting::create($data);
        else $mikrotik->update($data);

        // Update Session
        $request->session()->put(['ip' => $request->ip, 'user' => $request->user, 'password' => $request->password]);

        Activity::create([
            'log_name' => 'setting',
            'description' => 'Mengupdate konfigurasi MikroTik',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        if ($mikrotikWarning) return back()->with('warning', $mikrotikWarning);
        return back()->with('success', '✅ Konfigurasi MikroTik berhasil disimpan!');
    }

    private function updateBilling(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string',
            'company_email' => 'required|email',
            'billing_start_day' => 'required|integer|min:1|max:28',
            'due_days_after_period' => 'required|integer|min:1|max:30',
        ]);

        $billing = BillingConfig::first();
        $data = $request->only([
            'company_name', 'company_address', 'company_phone', 
            'company_email', 'billing_start_day', 'due_days_after_period'
        ]);

        if (!$billing) $billing = BillingConfig::create($data);
        else $billing->update($data);

        // Update Due Date Tagihan Unpaid
        $newDueDays = $request->due_days_after_period;
        $unpaidInvoices = \App\Models\Invoice::where('status', 'unpaid')->get();
        foreach ($unpaidInvoices as $inv) {
            $start = \Carbon\Carbon::parse($inv->billing_period_start);
            $inv->update(['due_date' => $start->copy()->addDays($newDueDays - 1)]);
        }

        Activity::create([
            'log_name' => 'setting',
            'description' => 'Mengupdate konfigurasi Billing',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return back()->with('success', '✅ Konfigurasi Billing berhasil disimpan!');
    }

    private function updateWhatsapp(Request $request)
    {
        $request->validate([
            'wa_invoice_template' => 'required|string',
        ]);

        $billing = BillingConfig::first();
        if ($billing) {
            $billing->update(['wa_invoice_template' => $request->wa_invoice_template]);
        }

        Activity::create([
            'log_name' => 'setting',
            'description' => 'Mengupdate template WhatsApp',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return back()->with('success', '✅ Template WhatsApp berhasil disimpan!');
    }
}
