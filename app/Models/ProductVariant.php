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

        // Thuộc tính
        'attribute_name',
        'attribute_value',

        // Giá bán
        'price',
        'original_price',

        // 🔥 GIÁ VỐN (QUAN TRỌNG CHO LỢI NHUẬN)
        'cost_price',

        // Kho
        'stock',
        'sold_quantity',

        'is_active',
    ];

    protected $casts = [
        'price'          => 'float',
        'original_price' => 'float',
        'cost_price'     => 'float',
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

    // 🔥 Lịch sử nhập hàng
    public function stockImports(): HasMany
    {
        return $this->hasMany(StockImport::class, 'variant_id');
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

    public function activePromotion(): ?Promotion
    {
        return Promotion::query()
            ->where('type', 'product')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereHas('promotionProducts', function ($q) {
                $q->where('variant_id', $this->id);
            })
            ->first();
    }

    /**
     * Giá sau khuyến mãi
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

    public function getIsOnSaleAttribute(): bool
    {
        return $this->activePromotion() !== null;
    }

    public function isOnSale(): bool
    {
        return $this->is_on_sale;
    }

    public function getDiscountAmountAttribute(): float
    {
        return max(0, $this->price - $this->final_price);
    }

    public function getDiscountLabelAttribute(): ?string
    {
        $promotion = $this->activePromotion();

        if (!$promotion) {
            return null;
        }

        return $promotion->discount_type === 'percent'
            ? "-{$promotion->discount_value}%"
            : '-' . number_format($promotion->discount_value, 0, ',', '.') . 'đ';
    }

    /* =====================================================
        PROFIT (CHUẨN CHO DASHBOARD)
    ===================================================== */

    // Lãi trên 1 sản phẩm
    public function getProfitPerItemAttribute(): float
    {
        return $this->final_price - $this->cost_price;
    }
}