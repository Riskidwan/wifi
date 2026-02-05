<?php

namespace App\Http\Controllers;

use App\Models\RouterosAPI;
// use Illuminate\Http\Request;

class InterfaceController extends Controller
{
    public function index()
	{
		$ip = session()->get('ip');
		$user = session()->get('user');
		$password = session()->get('password');
		$API = new RouterosAPI();
		$API->debug = false;

		if ($API->connect($ip, $user, $password)) {

			$interface = $API->comm('/interface/print');

			$data = [
				'menu' => 'Ethernet',
				'interface' => $interface,
			];

            return view('interface.index', $data);

		} else {
            return redirect('failed');
		}
	}


    public function traffic($interface)
{
    $ip = session()->get('ip');
    $user = session()->get('user');
    $password = session()->get('password');
    $API = new RouterosAPI();
    $API->debug = false;

    if ($API->connect($ip, $user, $password)) {
        try {
            $traffic = $API->comm("/interface/monitor-traffic", [
                "interface" => $interface,
                "once" => "",
            ]);

            $tx = $traffic[0]['tx-bits-per-second'] ?? 0;
            $rx = $traffic[0]['rx-bits-per-second'] ?? 0;

            return response()->json([
                ['name' => 'Tx', 'data' => (int)$tx],
                ['name' => 'Rx', 'data' => (int)$rx]
            ]);
        } catch (\Exception $e) {
            return response()->json([['name' => 'Tx', 'data' => 0], ['name' => 'Rx', 'data' => 0]]);
        }
    }

    return response()->json([['name' => 'Tx', 'data' => 0], ['name' => 'Rx', 'data' => 0]]);
}
}
