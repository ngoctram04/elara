<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // XEM THÔNG TIN
    public function show()
    {
        return view('admin.profile.show', [
            'admin' => Auth::user()
        ]);
    }

    // FORM CHỈNH SỬA
    public function edit()
    {
        return view('admin.profile.edit', [
            'admin' => Auth::user()
        ]);
    }

    // CẬP NHẬT
    public function update(Request $request)
    {
        // ✅ VALIDATION (TĂNG SIZE AVATAR)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        $admin = User::findOrFail(Auth::id());

        // cập nhật thông tin cơ bản
        $admin->name = $request->name;
        $admin->email = $request->email;

        // đổi mật khẩu nếu có
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        // upload avatar
        if ($request->hasFile('avatar')) {

            // xoá avatar cũ nếu tồn tại
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }

            // lưu avatar mới
            $admin->avatar = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $admin->save();

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Cập nhật thông tin thành công');
    }
}