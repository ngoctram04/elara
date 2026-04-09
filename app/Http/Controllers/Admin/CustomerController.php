<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $this->autoUnlockExpiredCustomers();

        $query = User::query()
            ->where('role', 'customer');

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

        if ($request->filled('member_level')) {
            $query->where('member_level', $request->member_level);
        }

        if ($request->filled('status')) {
            $query->where('is_active', (int) $request->status);
        }

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
            ->paginate(10)
            ->withQueryString();

        foreach ($customers as $customer) {
            $customer->spending = (float) ($customer->total_spent ?? 0);
            $customer->yearly_spending = (float) ($customer->yearly_spent ?? 0);

            $customer->cancel_count = (int) Order::where('user_id', $customer->id)
                ->where('status', 4)
                ->where('cancelled_by', 'customer')
                ->where('updated_at', '>=', now()->subDays(7))
                ->count();

            $customer->lock_status_text = $this->getLockStatusText($customer);
            $customer->lock_until_text = $this->formatLockedUntil($customer->locked_until);
            $customer->remaining_lock_time = $this->getRemainingLockTime($customer->locked_until, $customer->is_active);
        }

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        abort_if($user->role !== 'customer', 404);

        $this->autoUnlockUserIfExpired($user);
        $user->refresh();

        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->get();

        $totalSpent = (float) ($user->total_spent ?? 0);

        $reviews = Review::with([
            'product',
            'images',
            'video',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $lockStatusText = $this->getLockStatusText($user);
        $lockUntilText = $this->formatLockedUntil($user->locked_until);
        $remainingLockTime = $this->getRemainingLockTime($user->locked_until, $user->is_active);

        return view('admin.customers.show', compact(
            'user',
            'orders',
            'reviews',
            'totalSpent',
            'lockStatusText',
            'lockUntilText',
            'remainingLockTime'
        ));
    }

    public function toggleStatus(Request $request, User $user)
    {
        abort_if($user->role !== 'customer', 404);

        if ($user->is_active) {
            $validated = $request->validate([
                'blocked_reason' => 'required|string|min:5|max:1000',
            ], [
                'blocked_reason.required' => 'Vui lòng nhập lý do khóa tài khoản',
                'blocked_reason.min'      => 'Lý do phải có ít nhất 5 ký tự',
                'blocked_reason.max'      => 'Lý do không được vượt quá 1000 ký tự',
            ]);

            $lockedFrom = now();
            $lockedUntil = now()->addDays(7);

            $user->update([
                'is_active'      => false,
                'blocked_reason' => $validated['blocked_reason'],
                'locked_until'   => $lockedUntil,
            ]);

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

        $user->update([
            'is_active'      => true,
            'blocked_reason' => null,
            'locked_until'   => null,
        ]);

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

    protected function autoUnlockExpiredCustomers(): void
    {
        User::where('role', 'customer')
            ->where('is_active', false)
            ->whereNotNull('locked_until')
            ->where('locked_until', '<=', now())
            ->update([
                'is_active'      => true,
                'blocked_reason' => null,
                'locked_until'   => null,
            ]);
    }

    protected function autoUnlockUserIfExpired(User $user): void
    {
        if (
            !$user->is_active &&
            !empty($user->locked_until) &&
            Carbon::parse($user->locked_until)->lessThanOrEqualTo(now())
        ) {
            $user->update([
                'is_active'      => true,
                'blocked_reason' => null,
                'locked_until'   => null,
            ]);
        }
    }

    protected function getLockStatusText(User $user): string
    {
        if ($user->is_active) {
            return 'Hoạt động';
        }

        return 'Đang khóa';
    }


    protected function formatLockedUntil($lockedUntil): ?string
    {
        if (empty($lockedUntil)) {
            return null;
        }

        return Carbon::parse($lockedUntil)->format('d/m/Y H:i');
    }


    protected function getRemainingLockTime($lockedUntil, bool $isActive): ?string
    {
        if ($isActive || empty($lockedUntil)) {
            return null;
        }

        $lockedUntil = Carbon::parse($lockedUntil);
        $now = now();

        if ($lockedUntil->lessThanOrEqualTo($now)) {
            return 'Sắp được mở khóa';
        }

        $totalMinutes = $now->diffInMinutes($lockedUntil);

        $days = intdiv($totalMinutes, 1440);
        $remainingMinutesAfterDays = $totalMinutes % 1440;

        $hours = intdiv($remainingMinutesAfterDays, 60);
        $minutes = $remainingMinutesAfterDays % 60;

        if ($days > 0) {
            return "{$days} ngày {$hours} giờ nữa";
        }

        if ($hours > 0) {
            return "{$hours} giờ {$minutes} phút nữa";
        }

        return "{$minutes} phút nữa";
    }
}