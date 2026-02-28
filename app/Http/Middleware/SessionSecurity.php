<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionSecurity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            $user = Auth::user();

            // Kalau belum ada session_id di DB, izinkan
            if (!$user->session_id) {
                return $next($request);
            }

            // Jika session berbeda → logout & redirect
            if ($user->session_id !== session()->getId()) {

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email'=>'Akun sedang digunakan di device lain']);
            }

        }

        return $next($request);
    }
}
