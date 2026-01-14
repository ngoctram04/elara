<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Promotion;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',

        // Thuộc tính biến thể
        'attribute_name',   // VD: Màu sắc / Dung tích
        'attribute_value',  // VD: Đỏ / 500ml

        'price',            // Giá gốc (KHÔNG BAO GIỜ UPDATE)
        'original_price',   // (optional – nếu muốn lưu giá niêm yết)
        'stock',
        'sold_quantity',
        'is_active',
    ];

    protected $casts = [
        'price'          => 'float',
        'original_price' => 'float',
        'stock'          => 'integer',
        'sold_quantity'  => 'integer',
        'is_active'      => 'boolean',
    ];

    /* =====================================================
        RELATIONS
    ===================================================== */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    public function promotionProducts(): HasMany
    {
        return $this->hasMany(PromotionProduct::class, 'variant_id');
    }

    /* =====================================================
        STOCK
    ===================================================== */

    public function availableStock(): int
    {
        return max(0, $this->stock - $this->sold_quantity);
    }

    public function isInStock(): bool
    {
        return $this->is_active && $this->availableStock() > 0;
    }

    /* =====================================================
        DISPLAY
    ===================================================== */

    public function displayName(): string
    {
        return "{$this->attribute_name}: {$this->attribute_value}";
    }

    /* =====================================================
        PROMOTION CORE
    ===================================================== */

    /**
     * Khuyến mãi sản phẩm đang áp dụng
     * - Ưu tiên biến thể
     * - Fallback theo sản phẩm
     */
    public function activePromotion(): ?Promotion
    {
        return Promotion::where('type', 'product')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereHas('promotionProducts', function ($q) {
                $q->where('variant_id', $this->id)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('variant_id')
                            ->where('product_id', $this->product_id);
                    });
            })
            ->first();
    }

    /**
     * Giá cuối cùng sau khuyến mãi
     * 👉 DÙNG DUY NHẤT THUỘC TÍNH NÀY
     */
    public function getFinalPriceAttribute(): float
    {
        $promotion = $this->activePromotion();

        if (!$promotion) {
            return (float) $this->price;
        }

        if ($promotion->discount_type === 'percent') {
            return max(0, round(
                $this->price * (1 - $promotion->discount_value / 100),
                0
            ));
        }

        return max(0, $this->price - $promotion->discount_value);
    }

    /**
     * Có đang giảm giá không
     * 👉 $variant->is_on_sale
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->activePromotion() !== null;
    }

    /**
     * Alias để dùng dạng hàm nếu cần
     * 👉 $variant->isOnSale()
     */
    public function isOnSale(): bool
    {
        return $this->is_on_sale;
    }

    /**
     * Số tiền giảm được
     */
    public function getDiscountAmountAttribute(): float
    {
        return max(0, $this->price - $this->final_price);
    }

    /**
     * Nhãn giảm giá (VD: -20% | -50.000đ)
     */
    public function getDiscountLabelAttribute(): ?string
    {
        $promotion = $this->activePromotion();

        if (!$promotion) {
            return null;
        }

        return $promotion->discount_type === 'percent'
            ? "-{$promotion->discount_value}%"
            : "-" . number_format($promotion->discount_value) . "đ";
    }
}