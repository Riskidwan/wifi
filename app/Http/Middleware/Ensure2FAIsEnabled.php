<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ensure2FAIsEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle($request, Closure $next)
{
    // Jangan ganggu halaman verify
    if ($request->routeIs('2fa.verify') || $request->routeIs('2fa.verify.post')) {
        return $next($request);
    }

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Kalau belum aktif 2FA → paksa setup
    if (!$user->two_factor_enabled) {
        if (!$request->routeIs('2fa.setup') && !$request->routeIs('2fa.store')) {
            return redirect()->route('2fa.setup');
        }
        return $next($request);
    }

    // Kalau sudah aktif tapi belum lolos OTP
    if (!session('2fa_passed')) {
        return redirect()->route('2fa.verify');
    }

    return $next($request);
}



}
