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
        /*
         |--------------------------------------------------------------------------
         | 1️⃣ VALIDASI FORM
         |--------------------------------------------------------------------------
         */
        $request->validate([
            'ip' => 'required|ip',
            'user' => 'required|string',
            'password' => 'nullable|string',

            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string',
            'company_email' => 'required|email',
            'billing_start_day' => 'required|integer|min:1|max:28',
            'due_days_after_period' => 'required|integer|min:1|max:30',
        ]);

        /*
         |--------------------------------------------------------------------------
         | 2️⃣ 🔥 TEST KONEKSI MIKROTIK DULU (PENTING)
         |--------------------------------------------------------------------------
         */
        try {
            $API = new RouterosAPI();

            $connected = $API->connect(
                $request->ip,
                $request->user,
                $request->password
            );

            if (!$connected) {
                return back()->withErrors([
                    'ip' => '❌ Gagal konek MikroTik. IP / Username / Password salah atau API belum aktif.'
                ])->withInput();
            }

            // test command kecil biar yakin benar-benar connect
            $API->comm('/system/identity/print');

            $API->disconnect();

        }
        catch (\Exception $e) {
            return back()->withErrors([
                'ip' => '❌ Tidak bisa terhubung ke MikroTik. Pastikan API aktif & kredensial benar.'
            ])->withInput();
        }

        /*
         |--------------------------------------------------------------------------
         | 3️⃣ SIMPAN MIKROTIK SETTING (kalau sukses)
         |--------------------------------------------------------------------------
         */
        $mikrotik = MikrotikSetting::first();

        if (!$mikrotik) {
            $mikrotik = MikrotikSetting::create([
                'ip' => $request->ip,
                'username' => $request->user,
                'password' => $request->password,
            ]);
        }
        else {
            $mikrotik->update([
                'ip' => $request->ip,
                'username' => $request->user,
                'password' => $request->password,
            ]);
        }

        /*
         |--------------------------------------------------------------------------
         | 4️⃣ SIMPAN BILLING CONFIG
         |--------------------------------------------------------------------------
         */
        $billing = BillingConfig::first();

        if (!$billing) {
            BillingConfig::create([
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_phone' => $request->company_phone,
                'company_email' => $request->company_email,
                'billing_start_day' => $request->billing_start_day,
                'due_days_after_period' => $request->due_days_after_period,
            ]);
        }
        else {
            $billing->update([
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_phone' => $request->company_phone,
                'company_email' => $request->company_email,
                'billing_start_day' => $request->billing_start_day,
                'due_days_after_period' => $request->due_days_after_period,
            ]);
        }

        // ==========================================
        // CASCADE: Update Jatuh Tempo Tagihan Unpaid
        // ==========================================
        $newDueDays = $request->due_days_after_period ?? 5;
        $unpaidInvoices = \App\Models\Invoice::where('status', 'unpaid')->get();

        foreach ($unpaidInvoices as $inv) {
            $start = \Carbon\Carbon::parse($inv->billing_period_start);
            $inv->update([
                'due_date' => $start->copy()->addDays($newDueDays - 1)
            ]);
        }

        /*
         |--------------------------------------------------------------------------
         | 5️⃣ SIMPAN KE SESSION (biar dipakai realtime)
         |--------------------------------------------------------------------------
         */
        $request->session()->put('ip', $request->ip);
        $request->session()->put('user', $request->user);
        $request->session()->put('password', $request->password);

        /*
         |--------------------------------------------------------------------------
         | 6️⃣ ACTIVITY LOG
         |--------------------------------------------------------------------------
         */
        Activity::create([
            'log_name' => 'setting',
            'description' => 'Mengupdate konfigurasi MikroTik & Billing',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return back()->with('success', '✅ Konfigurasi berhasil disimpan & koneksi MikroTik valid!');
    }
}
