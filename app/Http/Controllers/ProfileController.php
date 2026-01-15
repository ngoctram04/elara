<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang thông tin tài khoản (frontend)
     */
    public function edit(Request $request): View
    {
        return view('frontend.profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật thông tin cơ bản (tên, phone)
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        $user = $request->user();

        $user->name  = $request->name;
        $user->phone = $request->phone;

        $user->save();

        return back()->with('success', 'Cập nhật thông tin thành công');
    }

    /**
     * Cập nhật ảnh đại diện (avatar)
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png'],
        ]);

        $user = $request->user();

        // Xoá avatar cũ (nếu có)
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Lưu avatar mới
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Cập nhật ảnh đại diện thành công');
    }

    /**
     * Đổi mật khẩu
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công');
    }

    /**
     * Xoá tài khoản người dùng
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Tài khoản đã được xoá');
    }
}