<?php

namespace App\Services;

use App\Models\MikrotikSetting;
use App\Models\RouterosAPI;

class MikrotikService
{
    public function getCredentials()
    {
        $setting = MikrotikSetting::first();
        return [
            'ip' => $setting->ip,
            'user' => $setting->username,
            'password' => $setting->password,
        ];
    }

    public function isConnected()
    {
        $creds = $this->getCredentials();
        
        if (empty($creds['ip']) || empty($creds['user'])) {
            return false;
        }

        $API = new RouterosAPI();
        $API->timeout = 3;
        
        return $API->connect($creds['ip'], $creds['user'], $creds['password']);
    }
  /*
    |--------------------------------------------------------------------------
    | Enable / Disable PPPoE Secret
    |--------------------------------------------------------------------------
    */
   public function updatePPPoESecretStatus($username, $status)
{
    \Log::info("=== START PPPoE UPDATE ===");

    $setting = \App\Models\MikrotikSetting::first();

    if (!$setting) {
        \Log::error('Mikrotik setting kosong di DB');
        return false;
    }

    \Log::info("IP: ".$setting->ip);

    $API = new \App\Models\RouterosAPI();
    $API->debug = true; // 🔥 nyalakan debug
    $API->timeout = 5;

    if (!$API->connect($setting->ip, $setting->username, $setting->password)) {
        \Log::error("Gagal konek ke Mikrotik");
        return false;
    }

    \Log::info("Berhasil connect Mikrotik");

    $secrets = $API->comm('/ppp/secret/print', [
        '?name' => $username
    ]);

    \Log::info('Secret result: '.json_encode($secrets));

    if (empty($secrets)) {
        \Log::error("Secret tidak ditemukan");
        return false;
    }

    $secret = $secrets[0];

    $disabled = $status === 'inactive' ? 'yes' : 'no';

    $result = $API->comm('/ppp/secret/set', [
        '.id' => $secret['.id'],
        'disabled' => $disabled
    ]);

    \Log::info('Set result: '.json_encode($result));

    $API->disconnect();

    \Log::info("=== END PPPoE UPDATE ===");

    return true;
}
}
