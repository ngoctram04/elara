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
        return $this->hasOne(ProductImage::class)
            ->where('is_main', 1);
    }

    public function subImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->where('is_main', 0);
    }

    /**
     * 🔥 BIẾN THỂ
     * - THỨ TỰ QUYẾT ĐỊNH BIẾN THỂ MẶC ĐỊNH
     * - BIẾN THỂ ĐẦU TIÊN = MẶC ĐỊNH
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('id'); // hoặc orderBy('position')
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

    /**
     * 🔥 BIẾN THỂ HIỂN THỊ TRÊN CARD
     * - LUÔN LẤY BIẾN THỂ ĐẦU TIÊN
     * - FRONTEND KHÔNG SORT
     */
    public function displayVariant()
    {
        return $this->variants->first();
    }

    /* ======================
        🔥 FLASH SALE LOGIC
    ====================== */

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

    public function getIsFlashSaleAttribute(): bool
    {
        return !is_null($this->activeFlashPromotion());
    }

    public function getFlashDiscountPercentAttribute(): int
    {
        $promo = $this->activeFlashPromotion();

        if (!$promo || $promo->discount_type !== 'percent') {
            return 0;
        }

        return (int) $promo->discount_value;
    }

    public function getFlashOriginalPriceAttribute(): int
    {
        return (int) $this->min_price;
    }

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