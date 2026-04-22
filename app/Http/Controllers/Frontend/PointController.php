<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPointHistory;
use App\Models\Promotion;
use App\Models\PointReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Notifications\SystemNotification;

class PointController extends Controller
{

    public function history()
    {
        $histories = UserPointHistory::where('user_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('frontend.points.history', compact('histories'));
    }

    public function redeemPage()
    {
        $user = Auth::user();

        $levels = ['bronze', 'silver', 'gold', 'diamond'];
        $userIndex = array_search($user->member_level, $levels);

        if ($userIndex === false) {
            $userIndex = 0;
        }

        $now = now();

        $rewards = PointReward::where('is_active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('redeem_start_at')
                    ->orWhere('redeem_start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('redeem_end_at')
                    ->orWhere('redeem_end_at', '>=', $now);
            })
            ->get()
            ->filter(function ($reward) use ($levels, $userIndex) {
                if ($reward->member_level === 'all') {
                    return true;
                }

                $rewardIndex = array_search($reward->member_level, $levels);

                return $rewardIndex !== false && $rewardIndex <= $userIndex;
            });

        $redeemedRewardIds = DB::table('user_point_rewards')
            ->where('user_id', $user->id)
            ->pluck('point_reward_id')
            ->toArray();

        return view('frontend.points.redeem', compact(
            'user',
            'rewards',
            'redeemedRewardIds'
        ));
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:point_rewards,id'
        ]);

        DB::beginTransaction();

        try {
            $reward = PointReward::lockForUpdate()->findOrFail($request->reward_id);

            $user = User::where('id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (!$user) {
                throw new \Exception("User không tồn tại.");
            }

            if (!$reward->is_active) {
                throw new \Exception("Voucher này không còn khả dụng.");
            }

            $now = now();

            if ($reward->redeem_start_at && $now->lt($reward->redeem_start_at)) {
                throw new \Exception("Voucher chưa đến thời gian đổi.");
            }

            if ($reward->redeem_end_at && $now->gt($reward->redeem_end_at)) {
                throw new \Exception("Voucher đã hết thời gian đổi.");
            }

            $levels = ['bronze', 'silver', 'gold', 'diamond'];
            $userIndex = array_search($user->member_level, $levels);

            if ($userIndex === false) {
                $userIndex = 0;
            }

            if ($reward->member_level !== 'all') {
                $rewardIndex = array_search($reward->member_level, $levels);

                if ($rewardIndex === false || $rewardIndex > $userIndex) {
                    throw new \Exception("Hạng thành viên của bạn chưa đủ để đổi voucher này.");
                }
            }

            $alreadyRedeemed = DB::table('user_point_rewards')
                ->where('user_id', $user->id)
                ->where('point_reward_id', $reward->id)
                ->exists();

            if ($alreadyRedeemed) {
                throw new \Exception("Bạn đã đổi voucher này rồi.");
            }

            if ($user->loyalty_points < $reward->points_required) {
                throw new \Exception("Bạn không đủ điểm.");
            }

            $user->decrement('loyalty_points', $reward->points_required);
            $user->refresh();

            do {
                $code = 'POINT-' . strtoupper(Str::random(6));
            } while (Promotion::where('code', $code)->exists());

            $promotion = Promotion::create([
                'code' => $code,
                'name' => $reward->title,
                'type' => 'order',
                'discount_type' => $reward->discount_type,
                'discount_value' => $reward->discount_value,
                'min_order_value' => $reward->min_order_value,
                'max_discount' => $reward->max_discount,
                'usage_limit' => 1,
                'used_count' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($reward->valid_days),
                'is_active' => 1,
                'user_id' => $user->id,
            ]);

            DB::table('user_point_rewards')->insert([
                'user_id' => $user->id,
                'point_reward_id' => $reward->id,
                'promotion_id' => $promotion->id,
                'created_at' => now(),
            ]);

            UserPointHistory::create([
                'user_id' => $user->id,
                'points' => -$reward->points_required,
                'type' => 'redeem',
                'description' => "Đổi điểm lấy voucher: {$reward->title} (Mã: {$code})"
            ]);

            DB::commit();

            $user->notify(new SystemNotification([
                'title' => 'Đổi điểm thành công',
                'message' => 'Bạn đã nhận voucher: ' . $code,
                'url' => route('cart.index'),
                'type' => 'voucher',
                'meta' => [
                    'code' => $code,
                    'discount' => $reward->discount_value
                ]
            ]));

            return redirect()
                ->route('points.redeem.page')
                ->with('success', "Đổi thành công. Mã: {$code}");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}