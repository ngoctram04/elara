<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class CustomerController extends Controller
{

    /**
     * Danh sách khách hàng
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'customer');

        /*
        |--------------------------------
        | TÌM KIẾM
        |--------------------------------
        */
        if ($request->filled('keyword')) {

            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        /*
        |--------------------------------
        | LỌC HẠNG THÀNH VIÊN
        |--------------------------------
        */
        if ($request->filled('member_level')) {

            $query->where('member_level', $request->member_level);
        }

        /*
        |--------------------------------
        | LỌC TRẠNG THÁI
        |--------------------------------
        */
        if ($request->filled('status')) {

            $query->where('is_active', (int)$request->status);
        }

        /*
        |--------------------------------
        | SẮP XẾP
        |--------------------------------
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

        $customers = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------
        | ĐẾM SỐ ĐƠN HỦY TRONG 7 NGÀY
        |--------------------------------
        */
        foreach ($customers as $customer) {

            $customer->cancel_count = Order::where('user_id', $customer->id)
                ->where('status', 4) // trạng thái đã hủy
                ->where('cancelled_by', 'customer') // chỉ tính khách hủy
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->count();
        }

        return view('admin.customers.index', compact('customers'));
    }


    /**
     * Chi tiết khách hàng
     */
    public function show(User $user)
    {
        abort_if($user->role !== 'customer', 404);

        /*
        |--------------------------------
        | ĐƠN HÀNG
        |--------------------------------
        */
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        /*
        |--------------------------------
        | ĐÁNH GIÁ SẢN PHẨM
        |--------------------------------
        */
        $reviews = Review::with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view(
            'admin.customers.show',
            compact('user', 'orders', 'reviews')
        );
    }


    /**
     * Khóa / mở tài khoản
     */
    public function toggleStatus(Request $request, User $user)
    {
        abort_if($user->role !== 'customer', 404);

        /*
    |--------------------------------
    | KHÓA TÀI KHOẢN
    |--------------------------------
    */
        if ($user->is_active) {

            $validated = $request->validate([
                'blocked_reason' => 'required|string|min:5|max:1000',
            ], [
                'blocked_reason.required' => 'Vui lòng nhập lý do khóa tài khoản',
                'blocked_reason.min'      => 'Lý do phải có ít nhất 5 ký tự',
            ]);

            $lockedFrom = now();
            $lockedUntil = now()->addDays(7);

            $user->is_active = false;
            $user->blocked_reason = $validated['blocked_reason'];
            $user->locked_until = $lockedUntil;
            $user->save();

            /*
        |--------------------------------
        | GỬI MAIL THÔNG BÁO KHÓA
        |--------------------------------
        */
            Mail::send(
                'emails.account_blocked',
                [
                    'user' => $user,
                    'reason' => $validated['blocked_reason'],
                    'locked_from' => $lockedFrom->format('d/m/Y H:i'),
                    'locked_until' => $lockedUntil->format('d/m/Y H:i')
                ],
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Thông báo khóa tài khoản ELARA');
                }
            );

            return back()->with('success', 'Đã khóa tài khoản khách hàng (7 ngày)');
        }


        /*
    |--------------------------------
    | MỞ KHÓA TÀI KHOẢN
    |--------------------------------
    */
        $user->is_active = true;
        $user->blocked_reason = null;
        $user->locked_until = null;
        $user->save();


        /*
    |--------------------------------
    | GỬI MAIL THÔNG BÁO MỞ KHÓA
    |--------------------------------
    */
        Mail::send(
            'emails.account_unblocked',
            [
                'user' => $user
            ],
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Tài khoản ELARA đã được mở khóa');
            }
        );


        return back()->with('success', 'Đã mở khóa tài khoản khách hàng');
    }
}