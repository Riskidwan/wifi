<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MikrotikSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{

    /*
     =====================================
     LOGIN FORM
     =====================================
     */
    public function showLoginForm(Request $request)
    {
        $lockoutSeconds = 0;
        $key = Str::lower($request->input('email', session('_lockout_email', ''))) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $lockoutSeconds = RateLimiter::availableIn($key);
        }

        return view('auth.login', compact('lockoutSeconds'));
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }

    /*
     =====================================
     LOGIN PROCESS
     =====================================
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            session(['_lockout_email' => $request->input('email')]);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Tunggu {$seconds} detik."
            ])->with('lockout_seconds', $seconds);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);
            session(['_lockout_email' => $request->input('email')]);

            $attempts = RateLimiter::attempts($key);

            // Cek apakah setelah hit ini sudah mencapai batas
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);
                return back()->withErrors([
                    'email' => "Terlalu banyak percobaan login. Tunggu {$seconds} detik."
                ])->with('lockout_seconds', $seconds);
            }

            $sisa = 3 - $attempts;
            return back()->withErrors(['email' => "Email / Password salah. (Percobaan sisa {$sisa} kesempatan)"]);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        $user = Auth::user();

        /*
         ==============================
         SINGLE SESSION (AUTO TENDANG)
         ==============================
         */
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();


        $request->session()->regenerate();

        $user->update([
            'session_id' => session()->getId(),
            'login_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);




        /*
         ==============================
         LOGIN LOG
         ==============================
         */
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User ' . $user->name . ' login',
            'causer_id' => $user->id,
            'causer_type' => get_class($user),
        ]);

        /*
         ==============================
         CEK KONEKSI MIKROTIK
         ==============================
         */
        $mikrotikService = app(\App\Services\MikrotikService::class);

        if (!$mikrotikService->isConnected()) {
            return redirect()->route('setting.index')
                ->with('warning', '⚠️ MikroTik belum terhubung! Silakan konfigurasi koneksi MikroTik terlebih dahulu.');
        }

        // Set session credentials dari database agar dashboard bisa connect
        $setting = \App\Models\MikrotikSetting::first();
        if ($setting) {
            $request->session()->put('ip', $setting->ip);
            $request->session()->put('user', $setting->username);
            $request->session()->put('password', $setting->password);
        }

        return redirect()->route('dashboard.index');
    }




    /*
     =====================================
     LOGOUT
     =====================================
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->update(['session_id' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /*
     =====================================
     ADMIN CRUD
     =====================================
     */
    public function index()
    {
        $admins = User::all();
        return view('admin.index', compact('admins'))
            ->with('menu', 'admin');
    }

    public function create()
    {
        return view('admin.create')
            ->with('menu', 'admin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Activity::create([
            'log_name' => 'admin',
            'description' => 'Menambah admin ' . $user->name,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return redirect()->route('admin.index')
            ->with('success', 'Admin berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $admin = User::findOrFail($id);

        return view('admin.edit', compact('admin'))
            ->with('menu', 'admin');
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        Activity::create([
            'log_name' => 'admin',
            'description' => 'Mengupdate admin ' . $admin->name,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        return redirect()->route('admin.index')
            ->with('success', 'Admin berhasil diperbarui!');
    }

    public function destroy($id)
    {
        if ($id == auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user = User::findOrFail($id);

        Activity::create([
            'log_name' => 'admin',
            'description' => 'Menghapus admin ' . $user->name,
            'causer_id' => auth()->id(),
            'causer_type' => get_class(auth()->user()),
        ]);

        $user->delete();

        return back()->with('success', 'Admin berhasil dihapus!');
    }
}
