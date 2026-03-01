<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; 
class ProfileController extends Controller
{
    /**
     * Hiển thị trang thông tin tài khoản
     */
    public function edit(Request $request): View
    {
        return view('frontend.profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:15'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'        => ['nullable', 'in:male,female,other'],
        ], [
            'name.required'        => 'Vui lòng nhập tên',
            'name.max'             => 'Tên tối đa 255 ký tự',
            'phone.max'            => 'Số điện thoại tối đa 15 ký tự',
            'date_of_birth.date'   => 'Ngày sinh không hợp lệ',
            'date_of_birth.before' => 'Ngày sinh phải nhỏ hơn hôm nay',
            'gender.in'            => 'Giới tính không hợp lệ',
        ]);

        // ===== CHỐNG GIAN LẬN NGÀY SINH =====

        $newBirthday = $validated['date_of_birth'] ?? null;
        $oldBirthday = $user->date_of_birth;

        // Nếu đã từng sửa ngày sinh rồi và đang cố thay đổi tiếp
        if ($user->birthday_updated_at && $newBirthday != $oldBirthday) {
            return back()->withErrors([
                'date_of_birth' => 'Bạn chỉ được thay đổi ngày sinh một lần.'
            ])->withInput();
        }

        // Nếu thay đổi ngày sinh lần đầu
        if (!$user->birthday_updated_at && $newBirthday != $oldBirthday) {
            $user->birthday_updated_at = now();
        }

        // =====================================

        $user->fill([
            'name'          => $validated['name'],
            'phone'         => $validated['phone'] ?? null,
            'date_of_birth' => $newBirthday,
            'gender'        => $validated['gender'] ?? null,
        ]);

        if (!$user->isDirty()) {
            return back()->with('info', 'Không có thay đổi nào.');
        }

        $user->save();

        return back()->with('success', 'Cập nhật thông tin thành công.');
    }

    /**
     * Cập nhật avatar
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh',
            'avatar.image'    => 'File phải là hình ảnh',
            'avatar.mimes'    => 'Chỉ chấp nhận JPG, PNG hoặc WEBP',
            'avatar.max'      => 'Ảnh tối đa 2MB',
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Cập nhật ảnh đại diện thành công.');
    }

    /**
     * Đổi mật khẩu
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Nếu tài khoản đăng nhập Google (không có password)
        if (!$user->password) {
            return back()->with('warning', 'Tài khoản này không thể đổi mật khẩu.');
        }

        $validated = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required'         => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp',
            'password.min'              => 'Mật khẩu tối thiểu 8 ký tự',
        ]);

        // Sai mật khẩu hiện tại
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng.'
                ])
                ->withInput();
        }

        // ❗ Mật khẩu mới trùng mật khẩu cũ -> hiển thị tại ô password
        if (Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.'
                ])
                ->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }

    /**
     * Xóa tài khoản
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors([
                    'password' => 'Mật khẩu không đúng.'
                ])
                ->withInput();
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Tài khoản đã được xoá.');
    }
    public function membership()
    {
        $user = Auth::user();

        $histories = DB::table('user_point_histories')
        ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('frontend.profile.membership', compact('user', 'histories'));
    }
}