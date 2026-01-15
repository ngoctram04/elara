<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập
        if (!Auth::check()) {

            // Nếu là request API / AJAX
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated');
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // Không phải admin
        if ($user->role !== 'admin') {

            if ($request->expectsJson()) {
                abort(403, 'Forbidden');
            }

            abort(403);
        }

        // Là admin → cho qua
        return $next($request);
    }
}