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
    /* =========================================================
        LIST
    ========================================================= */
    public function index()
    {
        $promotions = Promotion::latest()->paginate(5);
        $rewards = PointReward::latest()->paginate(5);

        return view('admin.promotions.index', compact('promotions', 'rewards'));
    }

    /* =========================================================
        CREATE (Redirect chọn loại)
    ========================================================= */
    public function create()
    {
        return redirect()->route('admin.promotions.choose');
    }

    public function chooseType()
    {
        return view('admin.promotions.choose');
    }

    /* =========================================================
        CREATE – REWARD
    ========================================================= */
    public function createReward()
    {
        return view('admin.promotions.create_reward');
    }

    /* =========================================================
        CREATE – PRODUCT
    ========================================================= */
    public function createProduct()
    {
        $products = Product::with('variants')->get();

        $activeVariantIds = PromotionProduct::whereHas('promotion', function ($q) {
            $q->where('type', 'product')
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now());
        })
            ->pluck('variant_id')
            ->toArray();

        return view('admin.promotions.create_product', compact(
            'products',
            'activeVariantIds'
        ));
    }

    /* =========================================================
        CREATE – ORDER
    ========================================================= */
    public function createOrder()
    {
        return view('admin.promotions.create_order');
    }

    /* =========================================================
        STORE PROMOTION
    ========================================================= */
    public function store(Request $request)
    {
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

        // Check trùng product promotion
        if (
            $request->type === 'product'
            && $this->hasActiveProductConflict($request)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đang có khuyến mãi khác đang diễn ra'
                ])
                ->withInput();
        }

        $promotion = null;

        DB::transaction(function () use ($request, &$promotion) {
            $promotion = Promotion::create([
                'code'            => $request->type === 'order'
                    ? strtoupper($request->code)
                    : null,
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

            // PRODUCT PROMOTION
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

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO KHÁCH HÀNG KHI TẠO MÃ KHUYẾN MÃI
        |--------------------------------------------------------------------------
        */
        if ($promotion && $promotion->type === 'order'
        ) {
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

    /* =========================================================
        EDIT
    ========================================================= */
    public function edit(Promotion $promotion)
    {
        if ($promotion->type === 'product') {
            $products = Product::with('variants')->get();
            $selected = $promotion->promotionProducts;

            $activeVariantIds = PromotionProduct::whereHas('promotion', function ($q) use ($promotion) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('id', '!=', $promotion->id);
            })
                ->pluck('variant_id')
                ->toArray();

            return view('admin.promotions.edit_product', compact(
                'promotion',
                'products',
                'selected',
                'activeVariantIds'
            ));
        }

        return view('admin.promotions.edit_order', compact('promotion'));
    }

    /* =========================================================
        UPDATE
    ========================================================= */
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
            && $this->hasActiveProductConflict($request, $promotion)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đang có khuyến mãi khác đang diễn ra'
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

    /* =========================================================
        TOGGLE ACTIVE
    ========================================================= */
    public function toggle(Promotion $promotion)
    {
        $promotion->update([
            'is_active' => !$promotion->is_active
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái');
    }

    /* =========================================================
        CHECK TRÙNG PRODUCT
    ========================================================= */
    private function hasActiveProductConflict(Request $request, Promotion $ignore = null): bool
    {
        if (empty($request->products)) {
            return false;
        }

        $variantIds = collect($request->products)->flatten()->filter();

        return PromotionProduct::whereIn('variant_id', $variantIds)
            ->whereHas('promotion', function ($q) use ($ignore) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());

                if ($ignore) {
                    $q->where('id', '!=', $ignore->id);
                }
            })
            ->exists();
    }

    /* =========================================================
        STORE REWARD
    ========================================================= */
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
                'is_active'       => 1,
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | THÔNG BÁO CHO KHÁCH HÀNG KHI TẠO VOUCHER ĐỔI ĐIỂM
        |--------------------------------------------------------------------------
        */
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
                ]
            ]));
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Tạo voucher đổi điểm thành công');
    }

    /* =========================================================
        EDIT REWARD
    ========================================================= */
    public function editReward(PointReward $reward)
    {
        return view('admin.promotions.edit_reward', compact('reward'));
    }

    /* =========================================================
        UPDATE REWARD
    ========================================================= */
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
        ]);

        $reward->update([
            'title'           => $request->name,
            'points_required' => $request->points_required,
            'discount_type'   => $request->discount_type,
            'discount_value'  => $request->discount_value,
            'min_order_value' => $request->min_order_value,
            'max_discount'    => $request->max_discount,
            'valid_days'      => $request->valid_days,
        ]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Cập nhật voucher thành công');
    }

    /* =========================================================
        TOGGLE REWARD
    ========================================================= */
    public function toggleReward(PointReward $reward)
    {
        $reward->update([
            'is_active' => !$reward->is_active
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái voucher');
    }

    /* =========================================================
        CHỈ LẤY KHÁCH HÀNG, KHÔNG LẤY ADMIN
    ========================================================= */
    private function customerQuery()
    {
        $query = User::query();

        // Trường hợp bảng users có cột is_admin
        if (Schema::hasColumn('users', 'is_admin')) {
            $query->where('is_admin', 0);
        }

        // Trường hợp bảng users có cột role
        if (Schema::hasColumn('users', 'role')) {
            $query->where('role', '!=', 'admin');
        }

        // Trường hợp bảng users có cột user_type
        if (Schema::hasColumn('users', 'user_type')) {
            $query->where('user_type', '!=', 'admin');
        }

        return $query;
    }

    /* =========================================================
        GỬI THÔNG BÁO CHO KHÁCH HÀNG
    ========================================================= */
    private function notifyCustomers(SystemNotification $notification): void
    {
        $this->customerQuery()
            ->select('id')
            ->chunkById(100, function ($users) use ($notification) {
                foreach ($users as $user) {
                    $user->notify($notification);
                }
            });
    }
}