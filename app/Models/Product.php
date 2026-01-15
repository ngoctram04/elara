<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

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
        'total_sold',
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
        return $this->belongsToMany(Promotion::class, 'promotion_products');
    }

    /* ======================
        HELPERS (CHUNG)
    ====================== */

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function getMainImageUrlAttribute(): string
    {
        if ($this->mainImage && $this->mainImage->image_path) {
            return asset('storage/' . $this->mainImage->image_path);
        }

        return asset('images/no-image.png');
    }

    /* ======================
        🔥 FLASH SALE LOGIC
        (QUAN TRỌNG NHẤT)
    ====================== */

    /**
     * Promotion flash sale đang hiệu lực
     */

    public function activeFlashPromotion()
    {
        return $this->promotions()
            ->where('type', 'product') // KM theo sản phẩm
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('discount_value')
            ->first();
    }

    /* % giảm */
    public function getFlashDiscountPercentAttribute(): int
    {
        $promo = $this->activeFlashPromotion();

        if (!$promo || $promo->discount_type !== 'percent') {
            return 0;
        }

        return (int) $promo->discount_value;
    }

    /* Giá gốc */
    public function getFlashOriginalPriceAttribute(): int
    {
        return (int) $this->min_price;
    }

    /* Giá sau giảm */
    public function getFlashSalePriceAttribute(): int
    {
        $promo = $this->activeFlashPromotion();

        if (!$promo) {
            return (int) $this->min_price;
        }

        // Giảm theo %
        if ($promo->discount_type === 'percent') {
            return (int) round(
                $this->min_price * (100 - $promo->discount_value) / 100
            );
        }

        // Giảm theo số tiền
        if ($promo->discount_type === 'fixed') {
            return max(
                (int) ($this->min_price - $promo->discount_value),
                0
            );
        }

        return (int) $this->min_price;
    }

    /* Có đang flash sale không */
    public function getIsFlashSaleAttribute(): bool
    {
        return (bool) $this->activeFlashPromotion();
    }

}