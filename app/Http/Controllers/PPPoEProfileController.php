<?php

namespace App\Http\Controllers;

use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PPPoEProfileController extends Controller
{
    public function index()
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');
        $API = new RouterosAPI();
        $API->debug = false;

        if ($API->connect($ip, $user, $password)) {
            $profiles = $API->comm('/ppp/profile/print');

            $data = [
                'menu' => 'PPPoE Profile',
                'profiles' => $profiles,
                'profile' => $profiles,
                'total' => count($profiles),
            ];

            return view('pppoe.profile.index', $data);
        }

        return redirect('failed');
    }

    public function create()
    {
        return view('pppoe.profile.create');
    }

    public function store(Request $request)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');
        $API = new RouterosAPI();
        $API->debug = false;

        if ($API->connect($ip, $user, $password)) {
            $params = [
                'name' => $request->name,
                'local-address' => $request->local_address ?: '0.0.0.0',
                'remote-address' => $request->remote_address ?: '0.0.0.0',
                'rate-limit' => $request->rate_limit ?: '',
                'use-compression' => $request->use_compression ?? 'no',
                'use-encryption' => $request->use_encryption ?? 'no',
                'use-mpls' => $request->use_mpls ?? 'no',
                'use-upnp' => $request->use_upnp ?? 'no',
                'change-tcp-mss' => $request->change_tcp_mss ?? 'yes',
                'dns-server' => $request->dns_server ?: '',
                'session-timeout' => $request->session_timeout ?: '',
                'idle-timeout' => $request->idle_timeout ?: '',
                'comment' => $request->comment ?: '',
            ];

            // Hapus key jika nilainya kosong dan tidak wajib
            $params = array_filter($params, function ($value) {
                return $value !== '';
            });

            $API->comm('/ppp/profile/add', $params);

            Alert::success('Berhasil', 'Profil PPPoE berhasil ditambahkan.');
            return redirect()->route('pppoe.profile.index');
        }

        return redirect('failed');
    }

    public function edit($id)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');
        $API = new RouterosAPI();
        $API->debug = false;

        if ($API->connect($ip, $user, $password)) {
            $profile = $API->comm('/ppp/profile/print', [
                "?.id" => '*' . $id
            ]);

            if (empty($profile)) {
                Alert::error('Gagal', 'Profil tidak ditemukan.');
                return redirect()->route('pppoe.profile.index');
            }

            return view('pppoe.profile.edit', ['profile' => $profile[0]]);
        }

        return redirect('failed');
    }

    public function update(Request $request)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');
        $API = new RouterosAPI();
        $API->debug = false;

        if ($API->connect($ip, $user, $password)) {
            $params = [
                '.id' => $request->id,
                'name' => $request->name,
                'local-address' => $request->local_address ?: '0.0.0.0',
                'remote-address' => $request->remote_address ?: '0.0.0.0',
                'rate-limit' => $request->rate_limit ?: '',
                'use-compression' => $request->use_compression ?? 'no',
                'use-encryption' => $request->use_encryption ?? 'no',
                'use-mpls' => $request->use_mpls ?? 'no',
                'use-upnp' => $request->use_upnp ?? 'no',
                'change-tcp-mss' => $request->change_tcp_mss ?? 'yes',
                'dns-server' => $request->dns_server ?: '',
                'session-timeout' => $request->session_timeout ?: '',
                'idle-timeout' => $request->idle_timeout ?: '',
                'comment' => $request->comment ?: '',
            ];

            // Filter hanya field yang diisi (abaikan kosong jika tidak wajib)
            $updateParams = ['.id' => $request->id];
            foreach ($params as $key => $value) {
                if ($key !== '.id' && $value !== '') {
                    $updateParams[$key] = $value;
                }
            }

            $API->comm('/ppp/profile/set', $updateParams);

            Alert::success('Berhasil', 'Profil PPPoE berhasil diperbarui.');
            return redirect()->route('pppoe.profile.index');
        }

        return redirect('failed');
    }

    public function destroy($id)
    {
        $ip = session()->get('ip');
        $user = session()->get('user');
        $password = session()->get('password');
        $API = new RouterosAPI();
        $API->debug = false;

        if ($API->connect($ip, $user, $password)) {
            $API->comm('/ppp/profile/remove', ['.id' => '*' . $id]);

            Alert::success('Berhasil', 'Profil PPPoE berhasil dihapus.');
            return redirect()->route('pppoe.profile.index');
        }

        return redirect('failed');
    }
}