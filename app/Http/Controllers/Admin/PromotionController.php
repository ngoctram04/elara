<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PointReward;
use App\Models\User;
use App\Notifications\SystemNotification;

class PromotionController extends Controller
{

    public function index(Request $request)
    {
        $promotionQuery = Promotion::query();
        $rewardQuery = PointReward::query();


        if ($request->filled('search')) {
            $keyword = trim($request->search);

            $promotionQuery->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('type')) {
            $promotionQuery->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $promotionQuery->where('is_active', 1);
            } elseif ($request->status === 'inactive') {
                $promotionQuery->where('is_active', 0);
            }
        }

        if ($request->filled('progress')) {
            if ($request->progress === 'upcoming') {
                $promotionQuery->where('start_date', '>', now());
            } elseif ($request->progress === 'ongoing') {
                $promotionQuery->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            } elseif ($request->progress === 'expired') {
                $promotionQuery->where('end_date', '<', now());
            }
        }

        switch ($request->get('sort', 'new')) {
            case 'old':
                $promotionQuery->oldest();
                break;
            case 'name_asc':
                $promotionQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $promotionQuery->orderBy('name', 'desc');
                break;
            case 'discount_desc':
                $promotionQuery->orderBy('discount_value', 'desc')->latest('id');
                break;
            case 'discount_asc':
                $promotionQuery->orderBy('discount_value', 'asc')->latest('id');
                break;
            default:
                $promotionQuery->latest();
                break;
        }


        if ($request->filled('reward_search')) {
            $rewardKeyword = trim($request->reward_search);
            $rewardQuery->where('title', 'like', '%' . $rewardKeyword . '%');
        }

        if ($request->filled('reward_status')) {
            if ($request->reward_status === 'active') {
                $rewardQuery->where('is_active', 1);
            } elseif ($request->reward_status === 'inactive') {
                $rewardQuery->where('is_active', 0);
            }
        }

        if ($request->filled('reward_progress')) {
            if ($request->reward_progress === 'upcoming') {
                $rewardQuery->whereNotNull('redeem_start_at')
                    ->where('redeem_start_at', '>', now());
            } elseif ($request->reward_progress === 'ongoing') {
                $rewardQuery->where(function ($q) {
                    $q->whereNull('redeem_start_at')
                        ->orWhere('redeem_start_at', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('redeem_end_at')
                        ->orWhere('redeem_end_at', '>=', now());
                });
            } elseif ($request->reward_progress === 'expired') {
                $rewardQuery->whereNotNull('redeem_end_at')
                    ->where('redeem_end_at', '<', now());
            }
        }

        switch ($request->get('reward_sort', 'new')) {
            case 'old':
                $rewardQuery->oldest();
                break;
            case 'points_desc':
                $rewardQuery->orderBy('points_required', 'desc')->latest('id');
                break;
            case 'points_asc':
                $rewardQuery->orderBy('points_required', 'asc')->latest('id');
                break;
            default:
                $rewardQuery->latest();
                break;
        }

        $promotions = $promotionQuery->paginate(5, ['*'], 'promotions_page');
        $rewards = $rewardQuery->paginate(5, ['*'], 'rewards_page');

        return view('admin.promotions.index', compact('promotions', 'rewards'));
    }

 
    public function create()
    {
        return redirect()->route('admin.promotions.choose');
    }

    public function chooseType()
    {
        return view('admin.promotions.choose');
    }

 
    public function createReward()
    {
        return view('admin.promotions.create_reward');
    }


    public function createProduct()
    {
        $products = Product::with(['variants', 'mainImage'])->get();

        return view('admin.promotions.create_product', compact('products'));
    }

    public function createOrder()
    {
        return view('admin.promotions.create_order');
    }

    
    public function store(Request $request)
    {
        $request->merge([
            'code' => $request->filled('code') ? strtoupper(trim($request->code)) : null,
        ]);

        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:product,order,reward',
            'discount_value' => 'required|integer|min:1|max:100',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',

            'code' => [
                'nullable',
                'required_if:type,order',
                'string',
                'max:50',
                function ($attr, $value, $fail) {
                    if (!$value) {
                        return;
                    }

                    if (
                        Promotion::where('type', 'order')
                        ->where('code', $value)
                        ->exists()
                    ) {
                        $fail('Mã giảm giá đã tồn tại.');
                    }
                },
            ],

            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
        ]);

        if (
            $request->type === 'product'
            && $this->hasProductConflictByDateRange($request)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đã có khuyến mãi khác bị trùng thời gian áp dụng.'
                ])
                ->withInput();
        }

        $promotion = null;

        DB::transaction(function () use ($request, &$promotion) {
            $promotion = Promotion::create([
                'code'            => $request->type === 'order' ? $request->code : null,
                'name'            => $request->name,
                'type'            => $request->type,
                'discount_type'   => 'percent',
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'usage_limit'     => $request->usage_limit,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'is_active'       => $request->boolean('is_active'),
            ]);

            if ($promotion->type === 'product') {
                foreach ($request->products ?? [] as $productId => $variantIds) {
                    foreach ($variantIds as $variantId) {
                        PromotionProduct::create([
                            'promotion_id' => $promotion->id,
                            'product_id'   => $productId,
                            'variant_id'   => $variantId,
                        ]);
                    }
                }
            }
        });

        if ($promotion && $promotion->type === 'order') {
            $this->notifyCustomers(new SystemNotification([
                'title'   => 'Mã khuyến mãi mới',
                'message' => 'Cửa hàng vừa tạo mã khuyến mãi "' . $promotion->code . '" - giảm ' . $promotion->discount_value . '%.',
                'url'     => route('cart.index'),
                'type'    => 'promotion',
                'meta'    => [
                    'promotion_id'   => $promotion->id,
                    'code'           => $promotion->code,
                    'discount_value' => $promotion->discount_value,
                    'start_date'     => $promotion->start_date,
                    'end_date'       => $promotion->end_date,
                ]
            ]));
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Tạo khuyến mãi thành công');
    }


    public function edit(Promotion $promotion)
    {
        if ($promotion->type === 'product') {
            $products = Product::with(['variants', 'mainImage'])->get();
            $selected = $promotion->promotionProducts;

            return view('admin.promotions.edit_product', compact(
                'promotion',
                'products',
                'selected'
            ));
        }

        return view('admin.promotions.edit_order', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'discount_value'  => 'required|integer|min:1|max:100',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
        ]);

        if (
            $promotion->type === 'product'
            && $this->hasProductConflictByDateRange($request, $promotion)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đã có khuyến mãi khác bị trùng thời gian áp dụng.'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $promotion) {
            $promotion->update([
                'name'            => $request->name,
                'discount_type'   => 'percent',
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'usage_limit'     => $request->usage_limit,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'is_active'       => $request->boolean('is_active'),
            ]);

            if ($promotion->type === 'product') {
                PromotionProduct::where('promotion_id', $promotion->id)->delete();

                foreach ($request->products ?? [] as $productId => $variantIds) {
                    foreach ($variantIds as $variantId) {
                        PromotionProduct::create([
                            'promotion_id' => $promotion->id,
                            'product_id'   => $productId,
                            'variant_id'   => $variantId,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Cập nhật khuyến mãi thành công');
    }


    public function toggle(Promotion $promotion)
    {
        $newStatus = !$promotion->is_active;

        if ($newStatus) {
            if ($promotion->end_date && $promotion->end_date < now()) {
                return back()->with('error', 'Không thể kích hoạt khuyến mãi đã hết hạn.');
            }

            if ($promotion->type === 'product' && $this->promotionHasConflictWhenActivating($promotion)) {
                return back()->with('error', 'Không thể kích hoạt vì một số biến thể đang bị trùng thời gian với khuyến mãi sản phẩm khác.');
            }
        }

        $promotion->update([
            'is_active' => $newStatus
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái');
    }


    private function hasProductConflictByDateRange(Request $request, Promotion $ignore = null): bool
    {
        if (empty($request->products)) {
            return false;
        }

        $variantIds = collect($request->products)
            ->flatten()
            ->filter()
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        if (empty($variantIds)) {
            return false;
        }

        return PromotionProduct::whereIn('variant_id', $variantIds)
            ->whereHas('promotion', function ($q) use ($request, $ignore) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $request->end_date)
                    ->where('end_date', '>=', $request->start_date);

                if ($ignore) {
                    $q->where('id', '!=', $ignore->id);
                }
            })
            ->exists();
    }


    private function promotionHasConflictWhenActivating(Promotion $promotion): bool
    {
        $variantIds = PromotionProduct::where('promotion_id', $promotion->id)
            ->pluck('variant_id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (empty($variantIds)) {
            return false;
        }

        return PromotionProduct::whereIn('variant_id', $variantIds)
            ->whereHas('promotion', function ($q) use ($promotion) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('id', '!=', $promotion->id)
                    ->where('start_date', '<=', $promotion->end_date)
                    ->where('end_date', '>=', $promotion->start_date);
            })
            ->exists();
    }

    public function storeReward(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'discount_type'   => 'required|in:percent,fixed',
            'discount_value'  => 'required|numeric|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'valid_days'      => 'required|integer|min:1',
            'redeem_start_at' => 'nullable|date',
            'redeem_end_at'   => 'nullable|date|after_or_equal:redeem_start_at',
        ]);

        $reward = null;

        DB::transaction(function () use ($request, &$reward) {
            $reward = PointReward::create([
                'title'           => $request->name,
                'points_required' => $request->points_required,
                'member_level'    => 'bronze',
                'discount_type'   => $request->discount_type,
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'valid_days'      => $request->valid_days,
                'redeem_start_at' => $request->redeem_start_at,
                'redeem_end_at'   => $request->redeem_end_at,
                'is_active'       => 1,
            ]);
        });

        if ($reward) {
            $discountText = $reward->discount_type === 'percent'
                ? $reward->discount_value . '%'
                : number_format($reward->discount_value, 0, ',', '.') . 'đ';

            $this->notifyCustomers(new SystemNotification([
                'title'   => 'Voucher đổi điểm mới',
                'message' => 'Cửa hàng vừa thêm voucher đổi điểm "' . $reward->title . '" - giảm ' . $discountText . '.',
                'url'     => route('points.redeem.page'),
                'type'    => 'voucher',
                'meta'    => [
                    'reward_id'        => $reward->id,
                    'title'            => $reward->title,
                    'points_required'  => $reward->points_required,
                    'discount_type'    => $reward->discount_type,
                    'discount_value'   => $reward->discount_value,
                    'valid_days'       => $reward->valid_days,
                    'redeem_start_at'  => $reward->redeem_start_at,
                    'redeem_end_at'    => $reward->redeem_end_at,
                ]
            ]));
        }

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'rewards'])
            ->with('success', 'Tạo voucher đổi điểm thành công');
    }


    public function editReward(PointReward $reward)
    {
        return view('admin.promotions.edit_reward', compact('reward'));
    }

    public function updateReward(Request $request, PointReward $reward)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'discount_type'   => 'required|in:percent,fixed',
            'discount_value'  => 'required|numeric|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'valid_days'      => 'required|integer|min:1',
            'redeem_start_at' => 'nullable|date',
            'redeem_end_at'   => 'nullable|date|after_or_equal:redeem_start_at',
        ]);

        $reward->update([
            'title'           => $request->name,
            'points_required' => $request->points_required,
            'discount_type'   => $request->discount_type,
            'discount_value'  => $request->discount_value,
            'min_order_value' => $request->min_order_value,
            'max_discount'    => $request->max_discount,
            'valid_days'      => $request->valid_days,
            'redeem_start_at' => $request->redeem_start_at,
            'redeem_end_at'   => $request->redeem_end_at,
        ]);

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'rewards'])
            ->with('success', 'Cập nhật voucher thành công');
    }


    public function toggleReward(PointReward $reward)
    {
        $newStatus = !$reward->is_active;

        if ($newStatus) {
            if ($reward->redeem_end_at && $reward->redeem_end_at->lt(now())) {
                return back()->with('error', 'Không thể bật voucher đổi điểm đã hết hạn.');
            }
        }

        $reward->update([
            'is_active' => $newStatus
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái voucher');
    }

    private function customerQuery()
    {
        $query = User::query();

        if (Schema::hasColumn('users', 'is_admin')) {
            $query->where('is_admin', 0);
        }

        if (Schema::hasColumn('users', 'role')) {
            $query->where('role', '!=', 'admin');
        }

        if (Schema::hasColumn('users', 'user_type')) {
            $query->where('user_type', '!=', 'admin');
        }

        return $query;
    }


    private function notifyCustomers(SystemNotification $notification): void
    {
        $this->customerQuery()
            ->chunkById(100, function ($users) use ($notification) {
                foreach ($users as $user) {
                    $user->notify($notification);
                }
            });
    }
}