<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Activitylog\Models\Activity;

class PelangganController extends Controller
{
    // app/Http/Controllers/PelangganController.php
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Pelanggan::with('paket')->latest('id_pelanggan');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pelanggan', 'LIKE', "%{$search}%")
                    ->orWhere('nama_pelanggan', 'LIKE', "%{$search}%");
            });
        }
        $pelanggans = $query->get();
        $pakets = \App\Models\Paket::all();

        // 🔑 Ambil PPPoE Secrets dari MikroTik
        $pppoeSecrets = [];
        try {
            $ip = session('ip');
            $user = session('user');
            $password = session('password');

            if ($ip && $user && $password) {
                $API = new \App\Models\RouterosAPI();
                $API->debug = false;
                $API->timeout = 5;

                if ($API->connect($ip, $user, $password)) {
                    $secrets = $API->comm('/ppp/secret/print');
                    foreach ($secrets as $secret) {
                        // Ambil name dan password
                        $pppoeSecrets[] = [
                            'name' => $secret['name'] ?? '',
                            'password' => $secret['password'] ?? ''
                        ];
                    }
                    $API->disconnect();
                }
            }
        }
        catch (\Exception $e) {
            \Log::warning('Gagal ambil PPPoE secrets: ' . $e->getMessage());
        }

        return view('pelanggan.index', compact('pelanggans', 'pakets', 'search', 'pppoeSecrets'));
    }

    public function create()
    {
        $pakets = \App\Models\Paket::all();
        $pppoeSecrets = [];

        try {
            // Ambil konfigurasi MikroTik dari session
            $ip = session('ip');
            $user = session('user');
            $password = session('password');

            if ($ip && $user && $password) {
                $API = new RouterosAPI();
                $API->debug = false;
                $API->timeout = 5;

                if ($API->connect($ip, $user, $password)) {
                    // Ambil semua PPPoE Secret
                    $secrets = $API->comm('/ppp/secret/print');

                    foreach ($secrets as $secret) {
                        // Hanya ambil yang service-nya 'pppoe'
                        if (isset($secret['service']) && $secret['service'] === 'pppoe') {
                            $pppoeSecrets[] = [
                                'name' => $secret['name'] ?? '',
                                'password' => $secret['password'] ?? '',
                            ];
                        }
                    }

                    $API->disconnect();
                }
            }
        }
        catch (\Exception $e) {
            \Log::warning('Gagal ambil PPPoE secrets: ' . $e->getMessage());
        }

        return view('pelanggan.create', compact('pakets', 'pppoeSecrets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',

            'username_pppoe' => 'nullable|string',
            'password_pppoe' => 'nullable|string',
            'id_paket' => 'nullable|exists:pakets,id', // ✅ validasi relasi
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|array',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:10048',
            'google_maps_url' => [
                'nullable',
                'url',
                'regex:~^(https?://)?(www\.)?(maps\.app\.goo\.gl|google\.com/maps)~i'],
        ]);

        // 🔑 Generate kode pelanggan 4 digit (0001 s/d 9999)
        $lastPelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->first();
        $nextNumber = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;
        if ($nextNumber > 9999) {
            Alert::error('Error', 'Kode pelanggan penuh (maks. 9999 pelanggan).');
            return back();
        }
        $kodePelanggan = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate atau gunakan username/password PPPoE
        if ($request->pppoe_method === 'use_existing' && $request->existing_pppoe_username) {
            // Gunakan akun PPPoE yang sudah ada di MikroTik
            $username = $request->existing_pppoe_username;
            $password = $request->existing_pppoe_password;
        }
        else {
            // Generate baru atau dari input manual
            $username = $request->username_pppoe ?: strtolower(str_replace(' ', '_', $request->nama_pelanggan)) . '_' . rand(1000, 9999);
            $password = $request->password_pppoe ?: substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        }

        // Upload foto
        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('pelanggan', 'public');
            }
        }

        // Simpan ke database
        $pelanggan = Pelanggan::create([
            'kode_pelanggan' => $kodePelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'username_pppoe' => $username,
            'password_pppoe' => $password,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'status_akun' => 'active',
            'id_paket' => $request->id_paket, // ✅
            'foto' => $fotoPaths,
            'google_maps_url' => $request->google_maps_url,
        ]);

        // Buat atau Update di MikroTik
        if ($request->pppoe_method === 'use_existing') {
            // PPPoE sudah ada di MikroTik → update profile sesuai paket yang dipilih
            $this->updateExistingPPPoEProfile($pelanggan);
        }
        else {
            // PPPoE baru → buat secret baru di MikroTik
            $this->createPPPoESecret($pelanggan);
        }

        Alert::success('Berhasil', 'Pelanggan berhasil dibuat!');
        return redirect()->route('pelanggan.index');
    }

    private function createPPPoESecret($pelanggan)
    {
        $profileName = 'default';
        if ($pelanggan->paket && !empty($pelanggan->paket->nama_paket)) {
            $profileName = $pelanggan->paket->nama_paket;
        }

        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        // Validasi koneksi
        if (!$ip || !$user || !$password) {
            \Log::error("MikroTik belum dikonfigurasi. Pelanggan: {$pelanggan->nama_pelanggan}");
            Alert::error('Error', 'Konfigurasi MikroTik belum diisi!');
            return false;
        }

        $API = new RouterosAPI();
        $API->debug = false; // ⚠️ JANGAN PAKAI true DI PRODUKSI
        $API->timeout = 5;

        if (!$API->connect($ip, $user, $password)) {
            \Log::error("Gagal koneksi ke MikroTik. Pelanggan: {$pelanggan->nama_pelanggan}");
            Alert::error('Error', 'Gagal terhubung ke MikroTik.');
            return false;
        }

        $username = $pelanggan->username_pppoe;
        $passwordPppoe = $pelanggan->password_pppoe;

        // Cek duplikat username
        $existing = $API->comm('/ppp/secret/print', ['?name' => $username]);
        if (!empty($existing)) {
            \Log::warning("Username PPPoE sudah ada: {$username}. Pelanggan: {$pelanggan->nama_pelanggan}");
            Alert::error('Error', "Username PPPoE '{$username}' sudah digunakan!");
            $API->disconnect();
            return false;
        }

        // Buat secret
        $params = [
            'name' => $username,
            'password' => $passwordPppoe,
            'service' => 'pppoe',
            'profile' => $profileName,
            'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
        ];

        try {
            $result = $API->comm('/ppp/secret/add', $params);

            // Cek error dari MikroTik
            if (isset($result[0]) && isset($result[0]['!trap'])) {
                $errorMsg = $result[0]['message'] ?? 'Unknown error';
                \Log::error("Gagal buat PPPoE: {$errorMsg}. Pelanggan: {$pelanggan->nama_pelanggan}");
                Alert::error('Error', "Gagal buat akun PPPoE: {$errorMsg}");
                $API->disconnect();
                return false;
            }

            \Log::info("Berhasil buat PPPoE: {$username}. Pelanggan: {$pelanggan->nama_pelanggan}");
            $API->disconnect();
            return true;

        }
        catch (\Exception $e) {
            \Log::error("Exception saat buat PPPoE: " . $e->getMessage());
            Alert::error('Error', 'Terjadi kesalahan saat membuat akun PPPoE.');
            $API->disconnect();
            return false;
        }
    }

    /**
     * Update profile PPPoE yang sudah ada di MikroTik
     * agar sesuai dengan paket yang dipilih di form
     */
    private function updateExistingPPPoEProfile($pelanggan)
    {
        $profileName = 'default';
        if ($pelanggan->paket && !empty($pelanggan->paket->nama_paket)) {
            $profileName = $pelanggan->paket->nama_paket;
        }

        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user || !$password) {
            \Log::warning("MikroTik belum dikonfigurasi. Skip update profile: {$pelanggan->username_pppoe}");
            return;
        }

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if (!$API->connect($ip, $user, $password)) {
            \Log::error("Gagal koneksi MikroTik untuk update profile: {$pelanggan->username_pppoe}");
            return;
        }

        // Cari secret yang sudah ada
        $secrets = $API->comm('/ppp/secret/print', ['?name' => $pelanggan->username_pppoe]);

        if (!empty($secrets)) {
            $secret = $secrets[0];
            // Update profile dan password sesuai data dari form
            $API->comm('/ppp/secret/set', [
                '.id' => $secret['.id'],
                'profile' => $profileName,
                'password' => $pelanggan->password_pppoe,
                'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
            ]);
            \Log::info("Berhasil update profile PPPoE '{$pelanggan->username_pppoe}' ke '{$profileName}'");
        }
        else {
            \Log::warning("Secret PPPoE '{$pelanggan->username_pppoe}' tidak ditemukan di MikroTik");
        }

        $API->disconnect();
    }

    public function edit($id)
    {
        $pelanggan = Pelanggan::with('paket')->findOrFail($id);
        $pakets = \App\Models\Paket::all();
        return view('pelanggan.edit', compact('pelanggan', 'pakets'));
    }
    public function detail($id)
    {
        $pelanggan = Pelanggan::with('paket')->findOrFail($id);
        return view('pelanggan.detail', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'username_pppoe' => 'nullable|string',
            'password_pppoe' => 'nullable|string',
            'id_paket' => 'nullable|exists:pakets,id',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status_akun' => 'required|in:active,inactive', // ✅ tambahkan validasi status
            'foto' => 'nullable|array',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'google_maps_url' => [
                'nullable',
                'url',
                'regex:~^(https?://)?(www\.)?(maps\.app\.goo\.gl|google\.com/maps)~i'], ]);


        $pelanggan = Pelanggan::findOrFail($id);
        // 🔑 AMBIL DATA LAMA SEBELUM UPDATE
        $oldUsername = $pelanggan->username_pppoe;
        $oldPassword = $pelanggan->password_pppoe;
        $oldPaketId = $pelanggan->id_paket;
        $oldStatus = $pelanggan->status_akun;
        $newStatus = $request->status_akun;

        // ✅ Gabung foto lama + foto baru
        $fotoPaths = $pelanggan->foto ?? []; // ambil foto lama
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('pelanggan', 'public');
            }
        }

        // ✅ Update semua field
        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'username_pppoe' => $request->username_pppoe,
            'password_pppoe' => $request->password_pppoe,
            'id_paket' => $request->id_paket,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'status_akun' => $newStatus,
            'foto' => $fotoPaths,
            'google_maps_url' => $request->google_maps_url,
        ]);

        // ✅ Reload relasi paket setelah update
        $pelanggan->load('paket');

        // ✅ UPDATE SECRET DI MIKROTIK (username, password, atau profile berubah)
        $usernameChanged = $oldUsername !== $request->username_pppoe;
        $passwordChanged = $oldPassword !== $request->password_pppoe;
        $paketChanged = $oldPaketId != $request->id_paket;

        if ($paketChanged) {
            // Update UNPAID invoices with the new package details
            $newPaket = \App\Models\Paket::find($request->id_paket);
            if ($newPaket) {
                // Hitung ulang total amount
                $hargaDasar = $newPaket->harga ?? 0;
                $ppn = $newPaket->ppn_aktif ? ($hargaDasar * ($newPaket->ppn_persen / 100)) : 0;
                $diskon = $newPaket->diskon_aktif ? ($hargaDasar * ($newPaket->diskon_persen / 100)) : 0;
                $newTotalAmount = $hargaDasar + $ppn - $diskon;

                \App\Models\Invoice::where('pelanggan_id', $pelanggan->id_pelanggan)
                    ->where('status', 'unpaid')
                    ->update([
                    'paket_nama' => $newPaket->nama_paket,
                    'amount' => $hargaDasar,
                    'total_amount' => $newTotalAmount
                ]);
            }
        }

        if ($usernameChanged || $passwordChanged || $paketChanged) {
            // Update secret di MikroTik (username, password, profile)
            $this->updatePPPoESecret($oldUsername, $pelanggan);
        }

        // ✅ UPDATE STATUS DI MIKROTIK (active/inactive berubah)
        if ($oldStatus !== $newStatus) {
            $this->updatePPPoESecretStatusOnMikrotik($pelanggan->username_pppoe, $newStatus);
        }

        Activity::create([
            'log_name' => 'pelanggan',
            'description' => 'Update pelanggan ' . $pelanggan->nama_pelanggan,
            'subject_id' => $pelanggan->id_pelanggan,
            'subject_type' => Pelanggan::class ,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        Alert::success('Berhasil', 'Data pelanggan diperbarui!');
        return redirect()->route('pelanggan.index');
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::with('paket')->findOrFail($id);
        // ✅ Catat aktivitas HAPUS
        Activity::create([
            'log_name' => 'pelanggan',
            'description' => 'Menghapus pelanggan ' . $pelanggan->nama_pelanggan,
            'subject_id' => $pelanggan->id_pelanggan,
            'subject_type' => Pelanggan::class ,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);
        $this->deletePPPoESecret($pelanggan->username_pppoe);
        $pelanggan->delete();

        Alert::success('Berhasil', 'Pelanggan dihapus!');
        return redirect()->route('pelanggan.index');
    }

    private function deletePPPoESecret($username)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user)
            return;

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);
            foreach ($secrets as $s) {
                $API->comm('/ppp/secret/remove', ['.id' => $s['.id']]);
            }
            $API->disconnect();
        }
    }
    private function updatePPPoESecret($oldUsername, $pelanggan)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user)
            return;

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if ($API->connect($ip, $user, $password)) {
            // Cari secret lama berdasarkan username lama
            $oldSecrets = $API->comm('/ppp/secret/print', ['?name' => $oldUsername]);

            if (!empty($oldSecrets)) {
                $secret = $oldSecrets[0];

                // Ambil profil baru
                $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';

                // Update secret
                $params = [
                    '.id' => $secret['.id'],
                    'name' => $pelanggan->username_pppoe,
                    'password' => $pelanggan->password_pppoe,
                    'profile' => $profileName,
                    'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
                ];

                $API->comm('/ppp/secret/set', $params);
            }

            $API->disconnect();
        }








    
}
    public function updatePPPoESecretStatusOnMikrotik($username, $status)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user) {
            \Log::warning("Konfigurasi MikroTik belum ada");
            return;
        }

        $API = new RouterosAPI();
        $API->debug = false;
        $API->timeout = 5;

        if (!$API->connect($ip, $user, $password)) {
            \Log::error("Gagal koneksi MikroTik");
            return;
        }

        $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);

        if (empty($secrets)) {
            \Log::warning("Secret tidak ditemukan: $username");
            $API->disconnect();
            return;
        }

        $secret = $secrets[0];

        // 🔥 WAJIB string yes/no
        $disabled = $status === 'inactive' ? 'yes' : 'no';

        $result = $API->comm('/ppp/secret/set', [
            '.id' => $secret['.id'],
            'disabled' => $disabled,
        ]);

        // 🔥 DEBUG RESULT
        \Log::info([
            'username' => $username,
            'set_result' => $result,
            'disabled' => $disabled
        ]);

        // 🔥 jika disable → paksa disconnect
        if ($disabled === 'yes') {
            $active = $API->comm('/ppp/active/print', ['?name' => $username]);

            foreach ($active as $a) {
                $API->comm('/ppp/active/remove', ['.id' => $a['.id']]);
            }
        }

        $API->disconnect();
    }
    public function previewKode()
    {
        $lastPelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->first();
        $nextNumber = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;

        if ($nextNumber > 9999) {
            return response()->json(['kode' => '9999'], 400);
        }

        $kode = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return response()->json(['kode' => $kode]);
    }

    public function verifyPPPoEStatus($username)
    {
        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user)
            return false;

        $API = new RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);
            $API->disconnect();

            return !empty($secrets) && $secrets[0]['disabled'] === 'yes';
        }
        return false;
    }
}