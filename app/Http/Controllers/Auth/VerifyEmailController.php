<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Xác thực email và redirect về shop
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Nếu đã xác thực rồi thì về shop luôn
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/shop?verified=1');
        }

        // Đánh dấu đã xác thực
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Xác thực xong → về shop
        return redirect('/shop?verified=1');
    }
}