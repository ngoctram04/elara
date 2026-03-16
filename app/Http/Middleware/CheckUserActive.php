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
        if (Auth::check()) {

            // Lấy user mới nhất từ database
            $user = User::find(Auth::id());

            /*
            |--------------------------------
            | USER KHÔNG TỒN TẠI
            |--------------------------------
            */
            if (!$user) {

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
            | TÀI KHOẢN BỊ KHÓA
            |--------------------------------
            */
            if ((int) $user->is_active !== 1) {

                // Nếu có thời gian khóa
                if ($user->locked_until) {

                    $lockedUntil = Carbon::parse($user->locked_until);

                    // Nếu vẫn còn bị khóa
                    if (now()->lt($lockedUntil)) {

                        Auth::logout();

                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        return redirect()
                            ->route('login')
                            ->withErrors([
                                'email' => 'Tài khoản của bạn bị khóa đến ngày ' .
                                    $lockedUntil->format('d/m/Y') . '.',
                            ]);
                    }

                    /*
                    |--------------------------------
                    | HẾT HẠN KHÓA → MỞ LẠI TÀI KHOẢN
                    |--------------------------------
                    */
                    $user->is_active = 1;
                    $user->blocked_reason = null;
                    $user->locked_until = null;
                    $user->save();
                }

                /*
                |--------------------------------
                | KHÓA VĨNH VIỄN (KHÔNG CÓ locked_until)
                |--------------------------------
                */ else {

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
        }

        return $next($request);
    }
}