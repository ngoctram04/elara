<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * ================================
     * DANH SÁCH KHÁCH HÀNG
     * ================================
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'customer');

        /*
        |--------------------------------------------------------------------------
        | TÌM KIẾM
        |--------------------------------------------------------------------------
        */
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT('KH', LPAD(id, 4, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC HẠNG THÀNH VIÊN
        |--------------------------------------------------------------------------
        */
        if ($request->filled('member_level')) {
            $query->where('member_level', $request->member_level);
        }

        /*
        |--------------------------------------------------------------------------
        | LỌC TRẠNG THÁI
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where('is_active', (int) $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | SẮP XẾP
        |--------------------------------------------------------------------------
        */
        switch ($request->get('sort')) {
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
            ->withCount([
                'orders as orders' => function ($q) {
                    $q->where('status', 3);
                }
            ])
            ->withSum([
                'orders as spending' => function ($q) {
                    $q->where('status', 3);
                }
            ], 'grand_total')
            ->withSum([
                'orders as yearly_spending' => function ($q) {
                    $q->where('status', 3)
                        ->whereYear('created_at', now()->year);
                }
            ], 'grand_total')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ĐẾM ĐƠN HỦY TRONG 7 NGÀY
        |--------------------------------------------------------------------------
        */
        $customerIds = $customers->pluck('id')->all();

        $cancelCounts = Order::select('user_id', DB::raw('COUNT(*) as total'))
            ->whereIn('user_id', $customerIds)
            ->where('status', 4)
            ->where('cancelled_by', 'customer')
            ->where('updated_at', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        foreach ($customers as $customer) {
            $customer->cancel_count = (int) ($cancelCounts[$customer->id] ?? 0);
        }

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * ================================
     * CHI TIẾT KHÁCH HÀNG
     * ================================
     */
    public function show(User $user)
    {
        abort_if($user->role !== 'customer', 404);

        /*
        |--------------------------------------------------------------------------
        | LỊCH SỬ ĐƠN HÀNG
        |--------------------------------------------------------------------------
        */
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TỔNG CHI TIÊU (CHỈ TÍNH ĐƠN ĐÃ GIAO)
        |--------------------------------------------------------------------------
        */
        $totalSpent = Order::where('user_id', $user->id)
            ->where('status', 3)
            ->sum('grand_total');

        /*
        |--------------------------------------------------------------------------
        | ĐÁNH GIÁ SẢN PHẨM + ẢNH + VIDEO
        |--------------------------------------------------------------------------
        */
        $reviews = Review::with([
            'product',
            'images',
            'video',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('admin.customers.show', compact(
            'user',
            'orders',
            'reviews',
            'totalSpent'
        ));
    }

    /**
     * ================================
     * KHÓA / MỞ TÀI KHOẢN
     * ================================
     */
    public function toggleStatus(Request $request, User $user)
    {
        abort_if($user->role !== 'customer', 404);

        /*
        |--------------------------------------------------------------------------
        | KHÓA TÀI KHOẢN
        |--------------------------------------------------------------------------
        */
        if ($user->is_active) {
            $validated = $request->validate([
                'blocked_reason' => 'required|string|min:5|max:1000',
            ], [
                'blocked_reason.required' => 'Vui lòng nhập lý do khóa tài khoản',
                'blocked_reason.min'      => 'Lý do phải có ít nhất 5 ký tự',
                'blocked_reason.max'      => 'Lý do không được vượt quá 1000 ký tự',
            ]);

            $lockedFrom  = now();
            $lockedUntil = now()->addDays(7);

            $user->update([
                'is_active'      => false,
                'blocked_reason' => $validated['blocked_reason'],
                'locked_until'   => $lockedUntil,
            ]);

            /*
            |--------------------------------------------------------------------------
            | GỬI MAIL KHÓA
            |--------------------------------------------------------------------------
            */
            Mail::send(
                'emails.account_blocked',
                [
                    'user'         => $user,
                    'reason'       => $validated['blocked_reason'],
                    'locked_from'  => $lockedFrom->format('d/m/Y H:i'),
                    'locked_until' => $lockedUntil->format('d/m/Y H:i'),
                ],
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Thông báo khóa tài khoản ELARA');
                }
            );

            return back()->with('success', 'Đã khóa tài khoản khách hàng trong 7 ngày');
        }

        /*
        |--------------------------------------------------------------------------
        | MỞ KHÓA
        |--------------------------------------------------------------------------
        */
        $user->update([
            'is_active'      => true,
            'blocked_reason' => null,
            'locked_until'   => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | GỬI MAIL MỞ KHÓA
        |--------------------------------------------------------------------------
        */
        Mail::send(
            'emails.account_unblocked',
            [
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Tài khoản ELARA đã được mở khóa');
            }
        );

        return back()->with('success', 'Đã mở khóa tài khoản khách hàng');
    }
}