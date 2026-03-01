<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Xác thực email (không cần đăng nhập)
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // ================= KIỂM TRA LINK HỢP LỆ =================
        if (! URL::hasValidSignature($request)) {
            return redirect()
                ->route('login')
                ->with('error', 'Link xác thực không hợp lệ hoặc đã hết hạn.');
        }

        // ================= TÌM USER =================
        $user = User::find($id);

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản không tồn tại.');
        }

        // ================= KIỂM TRA HASH EMAIL =================
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            (string) $hash
        )) {
            return redirect()
                ->route('login')
                ->with('error', 'Xác thực email không hợp lệ.');
        }

        // ================= XÁC THỰC EMAIL =================
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // ================= REDIRECT LOGIN + TOAST =================
        return redirect()
            ->route('login')
            ->with('success', 'Xác thực email thành công! Vui lòng đăng nhập.');
    }
}