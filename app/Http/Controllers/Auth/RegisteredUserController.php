<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Hiển thị form đăng ký
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ VALIDATION
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // ✅ LƯU AVATAR (NẾU CÓ)
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // ✅ TẠO USER (KHÔNG LOGIN)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'avatar' => $avatarPath,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        // ✅ GỬI EMAIL XÁC THỰC
        event(new Registered($user));

        // ❌ KHÔNG login
        // ❌ KHÔNG redirect home

        // ✅ VỀ TRANG LOGIN
        return redirect()->route('login')->with(
            'status',
            'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.'
        );
    }
}