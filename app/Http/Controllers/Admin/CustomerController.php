<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Danh sách khách hàng
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'customer');

        /**
         * 🔍 TÌM KIẾM
         */
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        /**
         * 📌 LỌC TRẠNG THÁI
         */
        if ($request->filled('status')) {
            $query->where('is_active', (int) $request->status);
        }

        /**
         * 🔃 SẮP XẾP
         */
        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'active':
                $query->where('is_active', 1)->latest();
                break;

            case 'blocked':
                $query->where('is_active', 0)->latest();
                break;

            case 'newest':
            default:
                $query->latest();
                break;
        }

        $customers = $query->paginate(10)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Xem chi tiết khách hàng
     */
    public function show(User $user)
    {
        abort_if($user->role !== 'customer', 404);

        return view('admin.customers.show', compact('user'));
    }

    /**
     * Khóa / mở tài khoản khách hàng
     */
    public function toggleStatus(Request $request, User $user)
    {
        abort_if($user->role !== 'customer', 404);

        /**
         * 🔒 ĐANG HOẠT ĐỘNG → KHÓA
         */
        if ((bool) $user->is_active === true) {

            $validated = $request->validate([
                'blocked_reason' => 'required|string|min:5|max:1000',
            ], [
                'blocked_reason.required' => 'Vui lòng nhập lý do khóa tài khoản',
                'blocked_reason.min'      => 'Lý do phải có ít nhất 5 ký tự',
            ]);

            $user->is_active = false;
            $user->blocked_reason = $validated['blocked_reason'];
            $user->save();

            return back()->with('success', 'Đã khóa tài khoản khách hàng');
        }

        /**
         * 🔓 ĐANG BỊ KHÓA → MỞ
         */
        $user->is_active = true;
        $user->blocked_reason = null;
        $user->save();

        return back()->with('success', 'Đã mở khóa tài khoản khách hàng');
    }
}