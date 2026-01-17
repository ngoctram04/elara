<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Xác thực tài khoản + mật khẩu
        $request->authenticate();

        // Tạo lại session
        $request->session()->regenerate();

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ❌ CHẶN LOGIN NẾU TÀI KHOẢN BỊ KHÓA
        |--------------------------------------------------------------------------
        */
        if ((int) $user->is_active !== 1) {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | (OPTIONAL) ❌ CHẶN LOGIN NẾU CHƯA XÁC THỰC EMAIL
        |--------------------------------------------------------------------------
        */
        /*
        if (! $user->hasVerifiedEmail()) {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Vui lòng xác thực email trước khi đăng nhập.',
            ]);
        }
        */

        /*
        |--------------------------------------------------------------------------
        | REDIRECT SAU LOGIN
        |--------------------------------------------------------------------------
        */

        // Admin → dashboard
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // User thường → LUÔN về trang chủ "/"
        return redirect()->route('home');
    }

    /**
     * Đăng xuất
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}