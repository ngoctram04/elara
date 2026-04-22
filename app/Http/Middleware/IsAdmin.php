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

        if (!Auth::check()) {

  
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated');
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {

            if ($request->expectsJson()) {
                abort(403, 'Forbidden');
            }

            abort(403);
        }

        return $next($request);
    }
}