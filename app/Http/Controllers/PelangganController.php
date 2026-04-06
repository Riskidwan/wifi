<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Activitylog\Models\Activity;
use App\Models\Paket;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PelangganExport;
use App\Exports\PelangganTemplateExport;
use App\Imports\PelangganImport;

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
            Log::warning('Gagal ambil PPPoE secrets: ' . $e->getMessage());
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
            Log::warning('Gagal ambil PPPoE secrets: ' . $e->getMessage());
        }

        return view('pelanggan.create', compact('pakets', 'pppoeSecrets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'username_pppoe' => 'nullable|string',
            'password_pppoe' => 'nullable|string',
            'id_paket' => 'nullable|exists:pakets,id',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'norekening_briva' => 'nullable|string',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|array',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:10048',
            'google_maps_url' => [
                'nullable',
                'url',
                'regex:~^(https?://)?(www\.)?(maps\.app\.goo\.gl|google\.com/maps)~i'],
        ]);

        $lastPelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->first();
        $nextNumber = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;
        if ($nextNumber > 9999) {
            Alert::error('Error', 'Kode pelanggan penuh (maks. 9999 pelanggan).');
            return back();
        }
        $kodePelanggan = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        if ($request->pppoe_method === 'use_existing' && $request->existing_pppoe_username) {
            $username = $request->existing_pppoe_username;
            $password = $request->existing_pppoe_password;
        } else {
            $username = $request->username_pppoe ?: strtolower(str_replace(' ', '_', $request->nama_pelanggan)) . '_' . rand(1000, 9999);
            $password = $request->password_pppoe ?: substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        }

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('pelanggan', 'public');
            }
        }

        try {
            return DB::transaction(function () use ($kodePelanggan, $username, $password, $request, $fotoPaths) {
                $pelanggan = Pelanggan::create([
                    'kode_pelanggan' => $kodePelanggan,
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'username_pppoe' => $username,
                    'password_pppoe' => $password,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'norekening_briva' => $request->norekening_briva,
                    'alamat' => $request->alamat,
                    'status_akun' => 'active',
                    'id_paket' => $request->id_paket,
                    'foto' => $fotoPaths,
                    'google_maps_url' => $request->google_maps_url,
                ]);

                $success = false;
                if ($request->pppoe_method === 'use_existing') {
                    $success = $this->updateExistingPPPoEProfile($pelanggan);
                } else {
                    $success = $this->createPPPoESecret($pelanggan);
                }

                if (!$success) {
                    throw new \Exception('Gagal sinkronisasi ke MikroTik. Silahkan cek koneksi router.');
                }

                Alert::success('Berhasil', 'Pelanggan berhasil dibuat dan sinkron ke MikroTik!');
                return redirect()->route('pelanggan.index');
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['mikrotik' => $e->getMessage()]);
        }
    }

    private function createPPPoESecret($pelanggan)
    {
        $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';

        $ip = session('ip');
        $user = session('user');
        $password = session('password');

        if (!$ip || !$user || !$password) {
            Log::error("MikroTik belum dikonfigurasi. Pelanggan: {$pelanggan->nama_pelanggan}");
            return false;
        }

        $API = new RouterosAPI();
        if (!$API->connect($ip, $user, $password)) {
            Log::error("Gagal koneksi ke MikroTik. Pelanggan: {$pelanggan->nama_pelanggan}");
            return false;
        }

        $existing = $API->comm('/ppp/secret/print', ['?name' => $pelanggan->username_pppoe]);
        if (!empty($existing)) {
            Log::warning("Username PPPoE sudah ada: {$pelanggan->username_pppoe}");
            $API->disconnect();
            return false;
        }

        $result = $API->comm('/ppp/secret/add', [
            'name' => $pelanggan->username_pppoe,
            'password' => $pelanggan->password_pppoe,
            'service' => 'pppoe',
            'profile' => $profileName,
            'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
        ]);

        if (isset($result[0]) && isset($result[0]['!trap'])) {
            Log::error("Gagal buat PPPoE: " . ($result[0]['message'] ?? 'Unknown error'));
            $API->disconnect();
            return false;
        }

        $API->disconnect();
        return true;
    }

    private function updateExistingPPPoEProfile($pelanggan)
    {
        $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';
        $ip = session('ip'); $user = session('user'); $password = session('password');
        if (!$ip || !$user || !$password) return false;

        $API = new RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $pelanggan->username_pppoe]);
            if (!empty($secrets)) {
                $API->comm('/ppp/secret/set', [
                    '.id' => $secrets[0]['.id'],
                    'profile' => $profileName,
                    'password' => $pelanggan->password_pppoe,
                    'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
                ]);
            }
            $API->disconnect();
            return true;
        }
        return false;
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
            'status_akun' => 'required|in:active,inactive',
            'id_paket' => 'nullable|exists:pakets,id',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'google_maps_url' => ['nullable', 'url'],
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $oldUsername = $pelanggan->username_pppoe;
        $oldStatus = $pelanggan->status_akun;
        $newStatus = $request->status_akun;

        $fotoPaths = $pelanggan->foto ?? [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('pelanggan', 'public');
            }
        }

        try {
            return DB::transaction(function () use ($pelanggan, $request, $fotoPaths, $oldUsername, $oldStatus, $newStatus) {
                $pelanggan->update([
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'username_pppoe' => $request->username_pppoe,
                    'password_pppoe' => $request->password_pppoe,
                    'id_paket' => $request->id_paket,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'norekening_briva' => $request->norekening_briva,
                    'alamat' => $request->alamat,
                    'status_akun' => $newStatus,
                    'foto' => $fotoPaths,
                    'google_maps_url' => $request->google_maps_url,
                ]);

                if (!$this->syncToMikrotik($pelanggan, $oldUsername, $oldStatus, $newStatus)) {
                    throw new \Exception('Gagal update data di MikroTik. Silahkan cek koneksi router.');
                }

                Activity::create([
                    'log_name' => 'pelanggan',
                    'description' => 'Update pelanggan ' . $pelanggan->nama_pelanggan,
                    'subject_id' => $pelanggan->id_pelanggan,
                    'subject_type' => Pelanggan::class ,
                    'causer_id' => auth()->id(),
                    'causer_type' => get_class(auth()->user()),
                ]);

                Alert::success('Berhasil', 'Pelanggan berhasil diperbarui dan sinkron ke MikroTik!');
                return redirect()->route('pelanggan.index');
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['mikrotik' => $e->getMessage()]);
        }
    }

    private function syncToMikrotik($pelanggan, $oldUsername, $oldStatus, $newStatus)
    {
        if (!$this->updatePPPoESecret($oldUsername, $pelanggan)) return false;
        if ($oldStatus !== $newStatus) {
            if (!$this->updatePPPoESecretStatusOnMikrotik($pelanggan->username_pppoe, $newStatus)) return false;
        }
        return true;
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
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

    public function exportExcel()
    {
        return Excel::download(new PelangganExport, 'data_pelanggan_' . date('Y-m-d') . '.xlsx');
    }

    public function exportTemplate()
    {
        return Excel::download(new PelangganTemplateExport, 'template_import_pelanggan.xlsx');
    }

    private function linkOrCreatePPPoESecret($API, $pelanggan)
    {
        $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';

        // Cek apakah sudah ada
        $existing = $API->comm('/ppp/secret/print', ['?name' => (string)$pelanggan->username_pppoe]);

        if (!empty($existing)) {
            // Update Profile & Comment agar Sinkron
            return $API->comm('/ppp/secret/set', [
                '.id' => $existing[0]['.id'],
                'profile' => $profileName,
                'comment' => "Pelanggan: {$pelanggan->nama_pelanggan} (Imported)",
            ]);
        } else {
            // Buat baru jika belum ada
            return $API->comm('/ppp/secret/add', [
                'name' => $pelanggan->username_pppoe,
                'password' => $pelanggan->password_pppoe,
                'service' => 'pppoe',
                'profile' => $profileName,
                'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
            ]);
        }
    }

    private function deletePPPoESecret($username)
    {
        $ip = session('ip'); $user = session('user'); $password = session('password');
        if (!$ip || !$user) return;
        $API = new RouterosAPI();
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
        $ip = session('ip'); $user = session('user'); $password = session('password');
        if (!$ip || !$user) return false;
        $API = new RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $oldSecrets = $API->comm('/ppp/secret/print', ['?name' => $oldUsername]);
            if (!empty($oldSecrets)) {
                $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';
                $API->comm('/ppp/secret/set', [
                    '.id' => $oldSecrets[0]['.id'],
                    'name' => $pelanggan->username_pppoe,
                    'password' => $pelanggan->password_pppoe,
                    'profile' => $profileName,
                    'comment' => "Pelanggan: {$pelanggan->nama_pelanggan}",
                ]);
            }
            $API->disconnect();
            return true;
        }
        return false;
    }

    private function updatePPPoESecretStatusOnMikrotik($username, $status)
    {
        $ip = session('ip'); $user = session('user'); $password = session('password');
        if (!$ip || !$user) return false;
        $API = new RouterosAPI();
        if ($API->connect($ip, $user, $password)) {
            $secrets = $API->comm('/ppp/secret/print', ['?name' => $username]);
            if (!empty($secrets)) {
                $disabled = $status === 'inactive' ? 'yes' : 'no';
                $API->comm('/ppp/secret/set', ['.id' => $secrets[0]['.id'], 'disabled' => $disabled]);
                if ($disabled === 'yes') {
                    $active = $API->comm('/ppp/active/print', ['?name' => $username]);
                    foreach ($active as $a) {
                        $API->comm('/ppp/active/remove', ['.id' => $a['.id']]);
                    }
                }
            }
            $API->disconnect();
            return true;
        }
        return false;
    }

    public function previewKode()
    {
        $lastPelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->first();
        $nextNumber = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;
        $kode = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return response()->json(['kode' => $kode]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'data_text' => 'nullable|string',
            'file' => 'nullable|file|mimes:xlsx,xls,csv',
        ]);

        $rawText = '';

        // Jika ada file upload, ubah menjadi format teks tab-separated agar bisa diproses parser
        if ($request->hasFile('file')) {
            try {
                Excel::import(new PelangganImport, $request->file('file'));
                
                Alert::success('Berhasil', 'Impor data pelanggan berhasil dan tersinkron ke MikroTik!');
                return redirect()->route('pelanggan.index');
            } catch (\Exception $e) {
                Alert::error('Gagal', 'Gagal mengimpor file Excel: ' . $e->getMessage());
                return back();
            }
        } else {
            $rawText = $request->data_text;
        }

        if (empty($rawText)) {
            Alert::error('Gagal', 'Silahkan paste data atau upload file Excel.');
            return back();
        }

        $lines = explode("\n", str_replace("\r", "", $rawText));
        
        $pakets = Paket::all();
        $importedCount = 0;
        $errors = [];

        // 🔑 Persiapkan koneksi MikroTik satu kali untuk efisiensi
        $ip = session('ip'); $user_mt = session('user'); $pass_mt = session('password');
        $API = new RouterosAPI();
        $isMikrotikConnected = ($ip && $user_mt && $pass_mt) ? $API->connect($ip, $user_mt, $pass_mt) : false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Split by TAB (\t)
            $cols = explode("\t", $line);
            
            // Jika tidak ada TAB, coba split by multiple spaces (minimal 2 spasi)
            if (count($cols) < 2) {
                $cols = preg_split('/\s{2,}/', $line);
            }

            if (count($cols) < 2) continue; 

            $nama = '';
            $email = '';
            $no_hp = '';
            $alamat = '';
            $id_paket = null;
            $briva = '';
            $kodePelanggan = '';
            $username = '';
            $password = '';

            // Mapping cerdas
            foreach ($cols as $col) {
                $col = trim($col);
                if (empty($col)) continue;

                // 1. Email
                if (filter_var($col, FILTER_VALIDATE_EMAIL)) {
                    $email = $col;
                    continue;
                }

                // 2. No HP
                if (preg_match('/^(\'?08|\+62)/', $col)) {
                    $no_hp = str_replace("'", "", $col);
                    continue;
                }

                // 3. Paket
                foreach ($pakets as $p) {
                    if (stripos($col, $p->nama_paket) !== false) {
                        $id_paket = $p->id;
                        break;
                    }
                }

                // 4. BRIVA
                if (preg_match('/^\'?1141\d{11}/', $col)) { 
                    $briva = str_replace("'", "", $col);
                    continue;
                }

                // 5. Kode Pelanggan (Custom ID)
                // Cek jika kolom berisi angka saja, panjang 4-10 digit, dan bukan HP/BRIVA
                if (empty($kodePelanggan) && preg_match('/^\d{4,10}$/', $col) && $col !== $no_hp && $col !== $briva) {
                    // Cek apakah sudah ada di DB
                    if (!Pelanggan::where('kode_pelanggan', $col)->exists()) {
                        $kodePelanggan = $col;
                        continue;
                    }
                }

                // 6. Nama 
                if (empty($nama) && !is_numeric($col) && strlen($col) > 3 && !preg_match('/rt|rw|tambakrejo/i', $col)) {
                    $nama = $col;
                    continue;
                }

                // 7. Alamat
                if (empty($alamat) && (preg_match('/rt|rw|tambakrejo|banjaragung/i', $col) || (strlen($col) > 5 && $col != $nama))) {
                    $alamat = $col;
                    continue;
                }

                // 8. Username & Password (Cerdas)
                // Jika kolom berisi string tanpa spasi, pendek (5-15 huruf), dan bukan ID/Email
                if (empty($username) && preg_match('/^[a-zA-Z0-9._-]{5,20}$/', $col) && !filter_var($col, FILTER_VALIDATE_EMAIL) && !is_numeric($col)) {
                    $username = $col;
                } elseif (empty($password) && strlen($col) >= 4 && strlen($col) <= 15 && $col != $username && $col != $nama) {
                    $password = $col;
                }
            }
            
            // JIKA USERNAME TIDAK DITEMUKAN, PAKAI KODE PELANGGAN ATAU GENERATE
            if (empty($username)) {
                $username = !empty($kodePelanggan) ? $kodePelanggan : (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)) . '_' . rand(100, 999));
            }

            // 🔄 SINKRONISASI DENGAN MIKROTIK (Jika Terkoneksi)
            if ($isMikrotikConnected) {
                // Cari apakah username ini sudah ada di router
                $existing = $API->comm('/ppp/secret/print', ['?name' => (string)$username]);
                if (!empty($existing)) {
                    // Gunakan password asli dari MikroTik agar tidak berubah
                    $password = $existing[0]['password'] ?? $password;
                }
            }

            if (empty($password)) {
                $password = Str::random(8);
            }

            try {
                DB::transaction(function () use ($kodePelanggan, $nama, $username, $password, $email, $no_hp, $briva, $alamat, $id_paket, $isMikrotikConnected, $API) {
                    $pelanggan = Pelanggan::create([
                        'kode_pelanggan' => $kodePelanggan,
                        'nama_pelanggan' => $nama,
                        'username_pppoe' => $username,
                        'password_pppoe' => $password,
                        'email' => $email,
                        'no_hp' => $no_hp,
                        'norekening_briva' => $briva,
                        'alamat' => $alamat,
                        'status_akun' => 'active',
                        'id_paket' => $id_paket,
                    ]);

                    // Sinkron MikroTik jika koneksi ada
                    if ($isMikrotikConnected) {
                        $this->linkOrCreatePPPoESecret($API, $pelanggan);
                    }
                });
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris '{$nama}': " . $e->getMessage();
            }
        }

        if ($isMikrotikConnected) {
            $API->disconnect();
        }

        if (count($errors) > 0) {
            Alert::warning('Import Selesai', "Berhasil import $importedCount. Error di " . count($errors) . " baris.");
        } else {
            Alert::success('Berhasil', "Berhasil mengimport $importedCount pelanggan.");
        }

        return redirect()->route('pelanggan.index');
    }
}