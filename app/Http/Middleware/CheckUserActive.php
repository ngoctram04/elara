<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CheckUserActive
{
    /**
     * Ép logout user khi tài khoản bị khóa
     */
    public function handle(Request $request, Closure $next)
    {
        // Chỉ xử lý khi đã đăng nhập
        if (Auth::check()) {

            // 🔥 LẤY USER MỚI NHẤT TỪ DATABASE
            $user = User::query()->find(Auth::id());

            // ❌ User không tồn tại hoặc đã bị khóa
            if (! $user || (int) $user->is_active !== 1) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Tài khoản của bạn đã bị khóa.',
                    ]);
            }
        }

        return $next($request);
    }
}