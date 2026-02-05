<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MikrotikSetting; // Tambahkan ini
use App\Models\RouterosAPI;  
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;


class AdminAuthController extends Controller
{
    // 👉 HALAMAN LOGIN
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 👉 PROSES LOGIN
    // 👉 PROSES LOGIN
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

         // ✅ LOG AKTIVITAS LOGIN
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User ' . auth()->user()->name . ' berhasil login',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
            'properties' => ['ip' => $request->ip()],
        ]);

        // ✅ Ambil setting dari database
        $mikrotik = MikrotikSetting::first();

        // Jika belum ada data atau IP kosong
        if (!$mikrotik || empty($mikrotik->ip)) {
            return redirect()->route('setting.index')
                ->with('warning', 'Silakan isi konfigurasi MikroTik terlebih dahulu!');
        }

        // ✅ Simpan ke session
        $request->session()->put('ip', $mikrotik->ip);
        $request->session()->put('user', $mikrotik->username);
        $request->session()->put('password', $mikrotik->password);

        // ✅ Cek koneksi MikroTik
        try {
            $API = new RouterosAPI();
            $API->timeout = 5;
            
            if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                $API->disconnect();
                return redirect()->route('dashboard.index');
            } else {
                return redirect()->route('setting.index')
                    ->with('error', 'Gagal koneksi ke MikroTik!');
            }
        } catch (\Exception $e) {
            \Log::error("Koneksi MikroTik error: " . $e->getMessage());
            return redirect()->route('setting.index')
                ->with('error', 'Error koneksi ke MikroTik: ' . $e->getMessage());
        }
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->withInput();
}

    // 👉 LOGOUT
    public function logout(Request $request)
    {
        // ✅ LOG AKTIVITAS LOGOUT
    if (auth()->check()) {
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User ' . auth()->user()->name . ' logout',
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
            'properties' => ['ip' => $request->ip()],
        ]);
    }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // 👉 DAFTAR ADMIN
    public function index()
    {
        $admins = User::all();
        return view('admin.index', compact('admins'))
            ->with('menu', 'admin')
            ->with('submenu', '');
    }

    // 👉 HALAMAN TAMBAH ADMIN
    public function create()
    {
        return view('admin.create')
            ->with('menu', 'admin')
            ->with('submenu', '');
    }

    // 👉 SIMPAN ADMIN BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        Activity::create([
    'log_name' => 'admin',
    'description' => 'Menambah admin ' . $request->name,
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil ditambahkan!');
    }

    // 👉 HALAMAN EDIT ADMIN
    public function edit($id)
    {
        $admin = User::findOrFail($id);
        return view('admin.edit', compact('admin'))
            ->with('menu', 'admin')
            ->with('submenu', '');
    }

    // 👉 UPDATE ADMIN
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Di update()
Activity::create([
    'log_name' => 'admin',
    'description' => 'Mengupdate admin ' . $admin->name,
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admin.index')->with('success', 'Admin berhasil diperbarui!');
    }

    // 👉 HAPUS ADMIN
    public function destroy($id)
    {
        // Di destroy()
Activity::create([
    'log_name' => 'admin',
    'description' => 'Menghapus admin ' . $user->name,
    'causer_id' => auth()->id(),
    'causer_type' => get_class(auth()->user()),
]);
        if ($id == auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Admin berhasil dihapus!');
    }
}