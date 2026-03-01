<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Activitylog\Models\Activity;


class PaketInternetController extends Controller
{
    public function index()
    {
        $pakets = Paket::latest()->get();
        return view('paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|unique:pakets,nama_paket',
            'kecepatan' => 'nullable|string',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|string|max:50',
            'harga' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
            'diskon_aktif' => 'required|in:0,1',
            'diskon_persen' => 'nullable|integer|min:0|max:100',
            'ppn_aktif' => 'required|in:0,1',
            'ppn_persen' => 'nullable|integer|min:0|max:100',
        ]);

        $paket = Paket::create([
            'nama_paket' => $request->nama_paket,
            'kecepatan' => $request->kecepatan,
            'local_address' => $request->local_address ?: '192.168.2.1',
            'remote_address' => $request->remote_address ?: 'pppoe-pool',
            'harga' => $request->harga,
            'keterangan' => $request->keterangan,
            'diskon_aktif' => (bool)$request->diskon_aktif,
            'diskon_persen' => $request->diskon_persen ?? 0,
            'ppn_aktif' => (bool)$request->ppn_aktif,
            'ppn_persen' => $request->ppn_persen ?? 11, ]);

        $this->createPPPoEProfileOnMikrotik($paket);

        Activity::create([
            'log_name' => 'paket',
            'description' => 'Menambah paket ' . $request->nama_paket,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()), ]);

        Alert::success('Berhasil', 'Paket Internet dan PPPoE Profile berhasil dibuat!');
        return redirect()->route('paket.index');
    }

    // Di method createPPPoEProfileOnMikrotik
    private function createPPPoEProfileOnMikrotik($paket)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');

        \Log::info("Membuat profil di MikroTik: {$paket->nama_paket}");

        if (!$ip || !$user) {
            \Log::error("Gagal: konfigurasi MikroTik belum diisi");
            return;
        }

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if ($API->connect($ip, $user, $password)) {
            $params = [
                'name' => $paket->nama_paket,
                'rate-limit' => $paket->kecepatan ?: '',
                'local-address' => $paket->local_address ?: '192.168.2.1',
                'remote-address' => $paket->remote_address ?: 'pppoe-pool',
                'dns-server' => '8.8.8.8,8.8.4.4',
                'comment' => $paket->keterangan ?: "Paket: {$paket->nama_paket}",
                // 'comment' => '',
            ];
            $params = array_filter($params, fn($v) => $v !== '');

            try {
                $API->comm('/ppp/profile/add', $params);
                \Log::info("Berhasil buat profil: {$paket->nama_paket}");
            }
            catch (\Exception $e) {
                \Log::error("Gagal buat profil {$paket->nama_paket}: " . $e->getMessage());
            }
            $API->disconnect();
        }
        else {
            \Log::error("Gagal koneksi ke MikroTik saat buat {$paket->nama_paket}");
        }
    }

    public function edit($id)
    {
        $paket = Paket::findOrFail($id);
        return view('paket.edit', compact('paket'));
    }

    public function update(Request $request, $id)
    {
        $paket = Paket::findOrFail($id);
        $request->validate([
            'nama_paket' => 'required|string|unique:pakets,nama_paket,' . $paket->id,
            'kecepatan' => 'nullable|string',
            'harga' => 'required|integer|min:0', 'diskon_aktif' => 'required|in:0,1',
            'diskon_persen' => 'nullable|integer|min:0|max:100',
            'ppn_aktif' => 'required|in:0,1', // ✅
            'ppn_persen' => 'nullable|integer|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $oldName = $paket->getOriginal('nama_paket');
        $paket->update([
            'nama_paket' => $request->nama_paket,
            'kecepatan' => $request->kecepatan,
            'local_address' => $request->local_address ?: '192.168.2.1',
            'remote_address' => $request->remote_address ?: 'pppoe-pool',
            'harga' => $request->harga,
            'diskon_aktif' => (bool)$request->diskon_aktif, // ✅ perbaikan
            'diskon_persen' => $request->diskon_persen ?? 0,
            'ppn_aktif' => (bool)$request->ppn_aktif,
            'ppn_persen' => $request->ppn_persen ?? 11,
            'keterangan' => $request->keterangan, ]);

        // Update tagihan (invoices) yang berstatus unpaid dan menggunakan paket ini
        $hargaDasar = $paket->harga ?? 0;
        $ppn = $paket->ppn_aktif ? ($hargaDasar * ($paket->ppn_persen / 100)) : 0;
        $diskon = $paket->diskon_aktif ? ($hargaDasar * ($paket->diskon_persen / 100)) : 0;
        $newTotalAmount = $hargaDasar + $ppn - $diskon;

        \App\Models\Invoice::where('paket_nama', $oldName)
            ->where('status', 'unpaid')
            ->update([
            'paket_nama' => $paket->nama_paket,
            'amount' => $hargaDasar,
            'total_amount' => $newTotalAmount
        ]);

        // Hapus lama, buat baru
        $this->deletePPPoEProfileOnMikrotik($oldName);
        $this->createPPPoEProfileOnMikrotik($paket);
        Activity::create([
            'log_name' => 'paket',
            'description' => 'Mengupdate paket ' . $paket->nama_paket,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()), ]);

        Alert::success('Berhasil', 'Paket Internet diperbarui!');
        return redirect()->route('paket.index');
    }

    public function destroy($id)
    {
        $paket = Paket::findOrFail($id);
        $this->deletePPPoEProfileOnMikrotik($paket->nama_paket);
        $paket->delete();

        // Di destroy()
        Activity::create([
            'log_name' => 'paket',
            'description' => 'Menghapus paket ' . $paket->nama_paket,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()), ]);
        Alert::success('Berhasil', 'Paket Internet dihapus!');
        return redirect()->route('paket.index');
    }

    private function deletePPPoEProfileOnMikrotik($profileName)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');

        \Log::info("Menghapus profil di MikroTik: {$profileName}");

        if (!$ip || !$user) {
            \Log::error("Gagal: konfigurasi MikroTik belum diisi saat hapus");
            return;
        }

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if ($API->connect($ip, $user, $password)) {
            $profiles = $API->comm('/ppp/profile/print', ['?name' => $profileName]);
            if (empty($profiles)) {
                \Log::warning("Profil tidak ditemukan di MikroTik: {$profileName}");
            }
            else {
                foreach ($profiles as $prof) {
                    $API->comm('/ppp/profile/remove', ['.id' => $prof['.id']]);
                }
                \Log::info("Berhasil hapus profil: {$profileName}");
            }
            $API->disconnect();
        }
        else {
            \Log::error("Gagal koneksi ke MikroTik saat hapus {$profileName}");
        }
    }
}