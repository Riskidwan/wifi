<?php

namespace App\Imports;

use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\RouterosAPI;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PelangganImport implements ToModel, WithHeadingRow, WithValidation
{
    private $pakets;
    private $nextKode;
    private $mikrotikAPI;
    private $isMikrotikConnected = false;

    public function __construct()
    {
        $this->pakets = Paket::all();
        $lastPelanggan = Pelanggan::orderBy('id_pelanggan', 'desc')->first();
        $this->nextKode = $lastPelanggan ? (intval($lastPelanggan->kode_pelanggan) + 1) : 1;

        // Setup MikroTik connection once per import session
        try {
            $ip = session('ip');
            $user = session('user');
            $password = session('password');
            
            if ($ip && $user) {
                $this->mikrotikAPI = new RouterosAPI();
                $this->mikrotikAPI->debug = false;
                $this->mikrotikAPI->timeout = 5;
                if ($this->mikrotikAPI->connect($ip, $user, $password)) {
                    $this->isMikrotikConnected = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('MikroTik Import Connection Failed: ' . $e->getMessage());
        }
    }

    public function model(array $row)
    {
        $nama = $row['nama_pelanggan'] ?? null;
        if (empty($nama)) return null;

        // Clean keys for slugified headings
        $username = $row['username_pppoe'] ?? null;
        $password = $row['password_pppoe'] ?? null;
        $namaPaket = $row['nama_paket'] ?? null;
        $status = $row['status_akun'] ?? 'active';
        $email = $row['email'] ?? null;
        $noHp = $row['no_hp'] ?? null;
        $briva = $row['briva'] ?? null;
        $alamat = $row['alamat'] ?? null;
        $maps = $row['google_maps_url'] ?? null;

        if (empty($username)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)) . '_' . rand(100, 999);
        }

        // 🔄 MikroTik Sync & Validation
        if ($this->isMikrotikConnected) {
            $existing = $this->mikrotikAPI->comm('/ppp/secret/print', ['?name' => (string)$username]);
            if (!empty($existing)) {
                $mtPass = $existing[0]['password'] ?? '';
                if (empty($password)) {
                    $password = $mtPass; // Ambil password dari MikroTik jika di excel kosong
                } elseif ($password != $mtPass) {
                    // VALIDASI KETAT: Jika password di excel beda dengan MikroTik, SKIP.
                    Log::warning("Import Skip: Password mismatch for {$username}. Excel: {$password}, MikroTik: {$mtPass}");
                    return null;
                }
            }
        }

        if (empty($password)) {
            $password = Str::random(8); // Fallback password
        }

        // Package Mapping
        $id_paket = null;
        if ($namaPaket) {
            $paket = $this->pakets->first(function($p) use ($namaPaket) {
                return stripos($p->nama_paket, $namaPaket) !== false;
            });
            if ($paket) $id_paket = $paket->id;
        }

        // Auto-increment Kode Pelanggan (0001, 0002, ...)
        $kode = str_pad($this->nextKode++, 4, '0', STR_PAD_LEFT);

        $pelanggan = new Pelanggan([
            'kode_pelanggan' => $kode,
            'nama_pelanggan' => $nama,
            'username_pppoe' => $username,
            'password_pppoe' => $password,
            'email' => $email,
            'no_hp' => $noHp,
            'norekening_briva' => $briva,
            'alamat' => $alamat,
            'status_akun' => $status,
            'id_paket' => $id_paket,
            'google_maps_url' => $maps,
        ]);

        // Sinkronisasi ke MikroTik jika data baru
        if ($this->isMikrotikConnected) {
            $this->linkOrCreatePPPoESecret($pelanggan);
        }

        return $pelanggan;
    }

    private function linkOrCreatePPPoESecret($pelanggan)
    {
        $profileName = $pelanggan->paket ? $pelanggan->paket->nama_paket : 'default';
        $existing = $this->mikrotikAPI->comm('/ppp/secret/print', ['?name' => (string)$pelanggan->username_pppoe]);

        if (empty($existing)) {
            $this->mikrotikAPI->comm('/ppp/secret/add', [
                'name' => (string)$pelanggan->username_pppoe,
                'password' => (string)$pelanggan->password_pppoe,
                'service' => 'pppoe',
                'profile' => $profileName,
                'comment' => "Pelanggan: {$pelanggan->nama_pelanggan} (Imported)",
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'required',
        ];
    }

    public function __destruct()
    {
        if ($this->isMikrotikConnected && $this->mikrotikAPI) {
            $this->mikrotikAPI->disconnect();
        }
    }
}
