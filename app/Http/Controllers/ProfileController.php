<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        $user->fill([
            'name'          => $validated['name'],
            'phone'         => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
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
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh',
            'avatar.image'    => 'File phải là hình ảnh',
            'avatar.mimes'    => 'Chỉ chấp nhận JPG, PNG hoặc WEBP',
            'avatar.max'      => 'Ảnh tối đa 2MB',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

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

        if (!$user->password) {
            return back()->with('warning', 'Tài khoản này không thể đổi mật khẩu.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required'         => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp',
            'password.min'              => 'Mật khẩu tối thiểu 8 ký tự',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        $validated = $validator->validated();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->with('error', 'Mật khẩu hiện tại không đúng.')
                ->withInput();
        }

        if (Hash::check($validated['password'], $user->password)) {
            return back()
                ->with('error', 'Mật khẩu mới không được trùng với mật khẩu hiện tại.')
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
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->with('error', 'Mật khẩu không đúng.')
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

    /**
     * Trang thành viên / tích điểm
     */
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