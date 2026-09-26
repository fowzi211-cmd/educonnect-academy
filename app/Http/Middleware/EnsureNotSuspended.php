<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends the session of a user who was suspended while already logged in —
 * the login-time check in LoginForm alone would let an existing session
 * keep working until it expired.
 */
class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuspended()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'form.email' => __('Your account has been suspended. Please contact support.'),
            ]);
        }

        return $next($request);
    }
}
