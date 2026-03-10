<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckUserActive
{
    /**
     * Ép logout user khi tài khoản bị khóa
     */
    public function handle(Request $request, Closure $next)
    {
        // Chỉ xử lý khi user đã đăng nhập
        if (Auth::check()) {

            // Lấy user mới nhất từ database
            $user = User::query()->find(Auth::id());

            // Nếu user không tồn tại
            if (! $user) {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Tài khoản không tồn tại.',
                    ]);
            }

            /*
            |--------------------------------
            | KIỂM TRA TÀI KHOẢN BỊ KHÓA
            |--------------------------------
            */
            if ((int) $user->is_active !== 1) {

                // Nếu có thời gian khóa
                if ($user->locked_until) {

                    $lockedUntil = Carbon::parse($user->locked_until);

                    // Nếu chưa hết thời gian khóa
                    if (now()->lt($lockedUntil)) {

                        Auth::logout();

                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()
                            ->route('login')
                            ->withErrors([
                                'email' => 'Tài khoản của bạn bị khóa đến ngày '
                                    . $lockedUntil->format('d/m/Y') . '.',
                            ]);
                    }

                    /*
                    |--------------------------------
                    | HẾT THỜI GIAN KHÓA → MỞ LẠI
                    |--------------------------------
                    */
                    $user->is_active = 1;
                    $user->blocked_reason = null;
                    $user->locked_until = null;
                    $user->save();
                }
            }
        }

        return $next($request);
    }
}