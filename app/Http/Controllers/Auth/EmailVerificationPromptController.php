<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {

            if ($request->user()->is_admin) {
                return redirect()->intended(route('admin.reports.index', false));
            }

            return redirect()->intended(route('home', false));
        }

        return view('auth.verify-email');
    }
}