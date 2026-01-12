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
        // Chưa đăng nhập → quay về login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Lấy user hiện tại
        $user = $request->user();

        // Không phải admin
        if ($user->role != 1) {
            abort(403);
        }

        // Là admin
        return $next($request);
    }
}