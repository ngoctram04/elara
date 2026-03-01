<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Hiển thị form quên mật khẩu
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi link đặt lại mật khẩu
     */
    public function store(Request $request): RedirectResponse
    {
        // ================= VALIDATE =================
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
        ]);

        // ================= GỬI LINK =================
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // ================= THÀNH CÔNG =================
        if ($status === Password::RESET_LINK_SENT) {
            return back()
                ->with('success', 'Link đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra email của bạn.');
        }

        // ================= EMAIL KHÔNG TỒN TẠI =================
        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email này không tồn tại trong hệ thống.');
    }
}