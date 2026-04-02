<?php

namespace App\Http\Controllers;

use App\Models\RouterosAPI;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Activitylog\Models\Activity; 

class PPPoEController extends Controller
{
	public function secret()
{
    $ip = session()->get('ip');
    $user = session()->get('user');
    $password = session()->get('password');
    $API = new RouterosAPI();
    $API->debug = false;

    if ($API->connect($ip, $user, $password)) {
        $secret = $API->comm('/ppp/secret/print');
        $profiles = $API->comm('/ppp/profile/print');

        // Buat peta: ['*1' => 'platinum', '*2' => 'premium', ...]
        $profileMap = [];
        foreach ($profiles as $p) {
            $profileMap[$p['.id']] = $p['name'];
        }

        // Tambahkan nama profile ke setiap secret
        foreach ($secret as &$s) {
            $s['profile_name'] = $profileMap[$s['profile']] ?? $s['profile'];
			$s['status_text'] = $s['disabled'] ?? 'no'; // Tambahkan status text
        }

        $data = [
            'menu' => 'PPPoE',
            'totalsecret' => count($secret),
            'secret' => $secret,
            'profile' => $profiles, // tetap kirim untuk dropdown edit
        ];

        return view('pppoe.secret', $data);
    }

    return redirect('failed');
}



	public function add(Request $request)
	{
		$ip = session()->get('ip');
		$user = session()->get('user');
		$password = session()->get('password');
		$API = new RouterosAPI();
		$API->debug = false;

		if ($API->connect($ip, $user, $password)) {

			$API->comm('/ppp/secret/add', [
				'name' => $request['user'],
				'password' => $request['password'],
				'service' => $request['service'] == '' ? 'any' : $request['service'],
				'profile' => $request['profile'] == '' ? 'default' : $request['profile'],
				'local-address' => $request['localaddress'] == '' ? '0.0.0.0' : $request['localaddress'],
				'remote-address' => $request['remoteaddress'] == '' ? '0.0.0.0' : $request['remoteaddress'],
				'comment' => $request['comment'] == '' ? '' : $request['comment'],
			]);
			Activity::create([
    'log_name' => 'pppoe',
    'description' => 'Menambah PPPoE secret ' . $request->user,
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);

			// dd($request->all());

			Alert::success('Success', 'Selamat anda Berhasil menambhakan secret PPPoE');
			return redirect('pppoe/secret');
		} else {

			return redirect('failed');
		}
	}



	public function edit($id)
	{
		$ip = session()->get('ip');
		$user = session()->get('user');
		$password = session()->get('password');
		$API = new RouterosAPI();
		$API->debug = false;

		if ($API->connect($ip, $user, $password)) {

			$getuser = $API->comm('/ppp/secret/print', [
				"?.id" => '*' . $id,
			]);

			$secret = $API->comm('/ppp/secret/print');
			$profile = $API->comm('/ppp/profile/print');

			$data = [
				'user' => $getuser[0],
				'secret' => $secret,
				'profile' => $profile,
			];

			// dd($data);

			return view('pppoe.edit', $data);
		} else {

			return redirect('failed');
		}
	}



	public function update(Request $request)
{
    $API = new RouterosAPI();
    $API->connect(
        session('ip'),
        session('user'),
        session('password')
    );

    $params = [
        ".id" => $request['id'],
        'name' => $request['user'],
        'password' => $request['password'],
        'service' => $request['service'],
        'profile' => $request['profile'],
        'disabled' => $request['disabled'],
        'comment' => $request['comment'],
    ];

    if (!empty($request['localaddress'])) {
        $params['local-address'] = $request['localaddress'];
    }

    if (!empty($request['remoteaddress'])) {
        $params['remote-address'] = $request['remoteaddress'];
    }

    $result = $API->comm("/ppp/secret/set", $params);

    if (isset($result['!trap'])) {
        dd($result);
    }

    Alert::success('Success', 'Berhasil update secret');
    return redirect()->route('pppoe.secret');
}


	public function delete($id)
	{
		// Di delete()
Activity::create([
    'log_name' => 'pppoe',
    'description' => 'Menghapus PPPoE secret ID ' . $id,
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);
		$ip = session()->get('ip');
		$user = session()->get('user');
		$password = session()->get('password');
		$API = new RouterosAPI();
		$API->debug = false;

		

		if ($API->connect($ip, $user, $password)) {

			$API->comm('/ppp/secret/remove', [
				'.id' => '*' . $id
			],);

			Alert::success('Success', 'Selamat anda Berhasil menghapus secret PPPoE');
			return redirect('pppoe/secret');
		} else {

			return redirect('failed');
		}
	}



	public function active()
	{
		$ip = session()->get('ip');
		$user = session()->get('user');
		$password = session()->get('password');
		$API = new RouterosAPI();
		$API->debug = false;

		if ($API->connect($ip, $user, $password)) {

			$secretactive = $API->comm('/ppp/active/print');

			$data = [
				'totalsecretactive' => count($secretactive),
				'active' => $secretactive,
			];

			return view('pppoe.active', $data);
		} else {

			return redirect('failed');
		}
	}
}

// error_reporting(0);
