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
        // Chưa đăng nhập → về login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Không phải admin → cấm
        if ($user->role !== 'admin') {
            abort(403);
        }

        // Là admin → cho qua
        return $next($request);
    }
}