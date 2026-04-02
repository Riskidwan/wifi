<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;
use App\Models\RouterosAPI;
use RealRashid\SweetAlert\Facades\Alert;

class PaketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $pakets = Paket::latest()->get();

        return view('paket.index', compact('pakets'));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE (Tambah Paket + Auto Buat PPP Profile)
    |--------------------------------------------------------------------------
    */
   public function store(Request $request)
{
    $request->validate([
        'nama_paket'     => 'required',
        'kecepatan'      => 'required',
        'harga'          => 'required|numeric',
        'local_address'  => 'required',
        'remote_address' => 'required',
    ]);

    /*
    ======================
    SAVE DB
    ======================
    */
    $paket = Paket::create([
        'nama_paket'     => $request->nama_paket,
        'kecepatan'      => $request->kecepatan,
        'harga'          => $request->harga,
        'local_address'  => $request->local_address,
        'remote_address' => $request->remote_address,
        'keterangan'     => $request->keterangan,
        'diskon_aktif'   => $request->diskon_aktif,
        'diskon_persen'  => $request->diskon_persen,
        'ppn_aktif'      => $request->ppn_aktif,
        'ppn_persen'     => $request->ppn_persen,
    ]);

    /*
    ======================
    CONNECT API
    ======================
    */
    $API = new RouterosAPI();

    if (!$API->connect(session('ip'), session('user'), session('password'))) {
        Alert::error('Gagal', 'Tidak bisa konek ke MikroTik');
        return back();
    }

    /*
    ======================
    ADD PROFILE
    ======================
    */
    $API->comm('/ppp/profile/add', [
        'name'           => $paket->nama_paket,
        'local-address'  => $paket->local_address,
        'remote-address' => $paket->remote_address,
        'rate-limit'     => $paket->kecepatan,
    ]);

    /*
    ======================
    VALIDASI BENAR-BENAR ADA
    ======================
    */
    $check = $API->comm('/ppp/profile/print', [
        '?name' => $paket->nama_paket
    ]);

    if (empty($check)) {
        Alert::error('Gagal', 'Profile gagal dibuat di MikroTik (pool/gateway salah)');
        return back();
    }

    Alert::success('Berhasil', 'Paket + PPP Profile berhasil dibuat');
    return redirect()->route('paket.index');
}




    /*
    |--------------------------------------------------------------------------
    | DELETE (hapus paket + profile)
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $paket = Paket::findOrFail($id);

        $ip       = session('ip');
        $user     = session('user');
        $password = session('password');

        $API = new RouterosAPI();

        if ($API->connect($ip, $user, $password)) {

            $profile = $API->comm('/ppp/profile/print', [
                '?name' => $paket->nama_paket
            ]);

            if (!empty($profile)) {
                $API->comm('/ppp/profile/remove', [
                    '.id' => $profile[0]['.id']
                ]);
            }
        }

        $paket->delete();

        Alert::success('Berhasil', 'Paket + Profile terhapus.');
        return back();
    }
}
