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
        $rawUsername = $row['username_pppoe'] ?? null;
        $nama = $row['nama_pelanggan'] ?? null;
        $password = $row['password_pppoe'] ?? null;

        if (empty($rawUsername) && empty($nama) && empty($password)) return null;

        $username = $rawUsername;
        $kode = null;

        // 1. Detect Combined "Username / Password" format
        if (!empty($username) && Str::contains($username, ' / ')) {
            $parts = explode(' / ', $username);
            $username = trim($parts[0]);
            if (empty($password)) {
                $password = trim($parts[1]);
            }
        }

        // 2. MikroTik REVERSE LOOKUP (By Password)
        // If username is unknown, try finding it in MikroTik using the password
        if (empty($username) && !empty($password) && $this->isMikrotikConnected) {
            $foundSecrets = $this->mikrotikAPI->comm('/ppp/secret/print', ['?password' => (string)$password]);
            if (!empty($foundSecrets)) {
                $username = $foundSecrets[0]['name'] ?? null;
                // If we found a name, let's also try to extract information from it later
                Log::info("Reverse Lookup: Found username '{$username}' in MikroTik using password '{$password}'");
            }
        }

        // 3. Detect Pattern [KODE]_[NAMA]@[DOMAIN]
        // Example: 0001_WASMIATI_INDAH@pikenet
        if (!empty($username) && preg_match('/^(\d+)_([^@]+)(?:@.*)?$/', $username, $matches)) {
            $extractedKode = $matches[1];
            $extractedNama = str_replace(['_', '.'], ' ', $matches[2]);

            if (empty($kode)) {
                $kode = $extractedKode;
            }
            if (empty($nama)) {
                $nama = ucwords(strtolower($extractedNama));
            }

            // Sync nextKode to avoid duplicates
            $numericKode = intval($extractedKode);
            if ($numericKode >= $this->nextKode) {
                $this->nextKode = $numericKode + 1;
            }
        }

        // 4. Password-to-Code Mapping (If code still null and password is 4-digit number)
        if (empty($kode) && !empty($password) && preg_match('/^\d{4}$/', $password)) {
            $kode = $password;
            
            // Sync nextKode
            $numericKode = intval($kode);
            if ($numericKode >= $this->nextKode) {
                $this->nextKode = $numericKode + 1;
            }
        }

        // 5. Finalize Name & Username if still empty
        if (empty($nama)) {
            $nama = $username;
        }

        if (empty($username)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)) . '_' . rand(100, 999);
        }

        if (empty($nama)) { // Fallback if still null
            $nama = "User " . ($kode ?? rand(1000, 9999));
        }

        // 6. Finalize Kode if still empty
        if (empty($kode)) {
            $kode = str_pad($this->nextKode++, 4, '0', STR_PAD_LEFT);
        }

        // Clean keys for slugified headings
        $namaPaket = $row['nama_paket'] ?? $row['paket'] ?? null;
        $status = $row['status_akun'] ?? $row['status'] ?? 'active';
        $email = $row['email'] ?? null;
        $noHp = $row['no_hp'] ?? $row['hp'] ?? $row['telepon'] ?? null;
        $briva = $row['briva'] ?? $row['norekening_briva'] ?? $row['no_rekening_briva'] ?? $row['nomor_rekening_briva'] ?? null;
        $alamat = $row['alamat'] ?? null;
        $maps = $row['google_maps_url'] ?? $row['maps'] ?? $row['google_maps'] ?? null;

        // 🔄 MikroTik Sync & Validation (Fetch password if still empty)
        if ($this->isMikrotikConnected && !empty($username)) {
            $existing = $this->mikrotikAPI->comm('/ppp/secret/print', ['?name' => (string)$username]);
            if (!empty($existing)) {
                $mtPass = $existing[0]['password'] ?? '';
                if (empty($password)) {
                    $password = $mtPass; 
                } elseif ($password != $mtPass) {
                    Log::warning("Import Skip: Password mismatch for {$username}. Excel: {$password}, MikroTik: {$mtPass}");
                    return null;
                }
            }
        }

        if (empty($password)) {
            $password = Str::random(8); 
        }

        // Package Mapping
        $id_paket = null;
        if ($namaPaket) {
            $paket = $this->pakets->first(function($p) use ($namaPaket) {
                return stripos($p->nama_paket, $namaPaket) !== false;
            });
            if ($paket) $id_paket = $paket->id;
        }

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
            'nama_pelanggan' => 'nullable',
        ];
    }

    public function __destruct()
    {
        if ($this->isMikrotikConnected && $this->mikrotikAPI) {
            $this->mikrotikAPI->disconnect();
        }
    }
}
