<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckUserActive
{

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            $user = User::find(Auth::id());

   
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

            if ((int) $user->is_active !== 1) {

       
                if ($user->locked_until) {

                    $lockedUntil = Carbon::parse($user->locked_until);

                
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


                    $user->is_active = 1;
                    $user->blocked_reason = null;
                    $user->locked_until = null;
                    $user->save();
                }
                 else {

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