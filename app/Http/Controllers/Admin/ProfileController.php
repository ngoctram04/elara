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
    // ==============================
    // XEM THÔNG TIN
    // ==============================
    public function show()
    {
        $admin = Auth::user();

        if (!$admin instanceof User) {
            abort(403, 'Không xác định được người dùng');
        }

        return view('admin.profile.show', compact('admin'));
    }

    // ==============================
    // FORM CHỈNH SỬA
    // ==============================
    public function edit()
    {
        $admin = Auth::user();

        if (!$admin instanceof User) {
            abort(403, 'Không xác định được người dùng');
        }

        return view('admin.profile.edit', compact('admin'));
    }

    // ==============================
    // CẬP NHẬT
    // ==============================
    public function update(Request $request)
    {
        $admin = Auth::user();

        if (!$admin instanceof User) {
            return back()->with('error',
                'Phiên đăng nhập không hợp lệ.'
            );
        }

        // ===== VALIDATION =====
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',

            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',

            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:8|confirmed',

            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'password.min' => 'Mật khẩu mới phải ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'current_password.required_with' => 'Vui lòng nhập mật khẩu hiện tại',
            'date_of_birth.before' => 'Ngày sinh phải nhỏ hơn hôm nay',
            'gender.in' => 'Giới tính không hợp lệ',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.mimes' => 'Chỉ chấp nhận JPG, PNG hoặc WEBP',
            'avatar.max' => 'Ảnh tối đa 2MB',
        ]);

        // ===== CẬP NHẬT THÔNG TIN CƠ BẢN =====
        $admin->fill([
            'name'          => $validated['name'],
            'phone'         => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender'        => $validated['gender'] ?? null,
        ]);

        // Kiểm tra thay đổi thật sự
        $changed = $admin->isDirty();

        // ===== ĐỔI MẬT KHẨU =====
        if ($request->filled('password')) {

            // Sai mật khẩu hiện tại
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()
                    ->withErrors([
                        'current_password' => 'Mật khẩu hiện tại không đúng.'
                    ])
                    ->withInput();
            }

            // Trùng mật khẩu cũ
            if (Hash::check($validated['password'], $admin->password)) {
                return back()
                    ->withErrors([
                        'password' => 'Mật khẩu mới không được trùng mật khẩu cũ.'
                    ])
                    ->withInput();
            }

            $admin->password = Hash::make($validated['password']);
            $changed = true;
        }

        // ===== AVATAR =====
        if ($request->hasFile('avatar')) {

            if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
                Storage::disk('public')->delete($admin->avatar);
            }

            $admin->avatar = $request->file('avatar')->store('avatars', 'public');
            $changed = true;
        }

        // ===== KHÔNG CÓ THAY ĐỔI =====
        if (!$changed) {
            return back()->with('info', 'Không có thay đổi nào.');
        }

        // ===== LƯU =====
        $admin->save();

        return redirect()
        ->route('admin.profile.edit')
        ->with('success', 'Cập nhật thông tin thành công.');
    }
}