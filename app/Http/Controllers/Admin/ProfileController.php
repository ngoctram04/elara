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
        $admin = User::findOrFail(Auth::id());

        // ✅ VALIDATION
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',

            // email chỉ để hiển thị
            'email' => 'required|email|max:255',

            // 🔐 đổi mật khẩu
            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:8|confirmed',

            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'current_password.required_with' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.min' => 'Mật khẩu mới phải ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        $changed = false;

        /* ========= HỌ TÊN ========= */
        if ($admin->name !== $validated['name']) {
            $admin->name = $validated['name'];
            $changed = true;
        }

        /* ========= SỐ ĐIỆN THOẠI ========= */
        if ($admin->phone !== $validated['phone']) {
            $admin->phone = $validated['phone'];
            $changed = true;
        }

        /* ========= ĐỔI MẬT KHẨU ========= */
        if ($request->filled('password')) {

            // ❌ mật khẩu cũ sai
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng'
                ]);
            }

            $admin->password = Hash::make($validated['password']);
            $changed = true;
        }

        /* ========= AVATAR ========= */
        if ($request->hasFile('avatar')) {
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }

            $admin->avatar = $request->file('avatar')->store('avatars', 'public');
            $changed = true;
        }

        /* ========= KHÔNG CÓ GÌ THAY ĐỔI ========= */
        if (!$changed) {
            return back()->with('info', 'Không có thay đổi nào được cập nhật.');
        }

        $admin->save();

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }
}