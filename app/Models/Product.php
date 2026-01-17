<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'description',
        'short_description',
        'min_price',
        'max_price',
        'total_stock',
        'total_sold',   // ✅ dùng cho "Bán chạy"
        'is_active',
        'is_featured',
    ];

    /* ======================
        RELATIONS
    ====================== */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', 1);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(
            Promotion::class,
            'promotion_products',
            'product_id',
            'promotion_id'
        );
    }

    /* ======================
        HELPERS
    ====================== */

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function getMainImageUrlAttribute(): string
    {
        if ($this->relationLoaded('mainImage') && $this->mainImage?->image_path) {
            return asset('storage/' . $this->mainImage->image_path);
        }

        return asset('images/no-image.png');
    }

    /* ======================
        🔥 FLASH SALE LOGIC
    ====================== */

    /**
     * Lấy promotion đang hiệu lực (ưu tiên giảm nhiều nhất)
     */
    public function activeFlashPromotion()
    {
        return $this->promotions
            ->where('type', 'product')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->sortByDesc('discount_value')
            ->first();
    }

    /**
     * Có đang flash sale không
     */
    public function getIsFlashSaleAttribute(): bool
    {
        return !is_null($this->activeFlashPromotion());
    }

    /**
     * % giảm
     */
    public function getFlashDiscountPercentAttribute(): int
    {
        $promo = $this->activeFlashPromotion();

        if (!$promo || $promo->discount_type !== 'percent') {
            return 0;
        }

        return (int) $promo->discount_value;
    }

    /**
     * Giá gốc (min_price)
     */
    public function getFlashOriginalPriceAttribute(): int
    {
        return (int) $this->min_price;
    }

    /**
     * Giá sau giảm
     */
    public function getFlashSalePriceAttribute(): int
    {
        $promo = $this->activeFlashPromotion();
        $price = (int) $this->min_price;

        if (!$promo) {
            return $price;
        }

        if ($promo->discount_type === 'percent') {
            return max(
                (int) round($price * (100 - $promo->discount_value) / 100),
                0
            );
        }

        if ($promo->discount_type === 'fixed') {
            return max(
                (int) ($price - $promo->discount_value),
                0
            );
        }

        return $price;
    }
}