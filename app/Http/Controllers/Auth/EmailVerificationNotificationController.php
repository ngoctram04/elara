<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {

            if ($request->user()->is_admin) {
                return redirect()->intended(route('admin.reports.index', false));
            }

            return redirect()->intended(route('home', false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}