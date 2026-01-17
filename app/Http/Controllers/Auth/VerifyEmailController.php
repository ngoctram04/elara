<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Verify email KHÔNG CẦN LOGIN
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // 🔐 Check hash email
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            (string) $hash
        )) {
            abort(403);
        }

        // ✅ Verify nếu chưa verify
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // 🔐 Đảm bảo không còn login
        Auth::logout();

        return redirect()->route('login')
            ->with('verified', true);
    }
}