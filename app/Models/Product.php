<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'description',
        'min_price',
        'max_price',
        'is_active',
        'is_featured',
    ];

    protected $appends = [
        'reviews_avg_rating',
        'reviews_count',
    ];

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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('id');
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

    public function displayVariant()
    {
        return $this->variants->first();
    }

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
            return max((int) round($price * (100 - $promo->discount_value) / 100), 0);
        }

        if ($promo->discount_type === 'fixed') {
            return max((int) ($price - $promo->discount_value), 0);
        }

        return $price;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->sum('stock_quantity');
        }

        return (int) $this->variants()->sum('stock_quantity');
    }

    public function getTotalSoldAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->sum('sold_quantity');
        }

        return (int) $this->variants()->sum('sold_quantity');
    }

    public function questions()
    {
        return $this->hasMany(ProductQuestion::class)
            ->where('is_active', 1)
            ->latest();
    }

    public function getReviewsAvgRatingAttribute(): float
    {
        if (array_key_exists('reviews_avg_rating', $this->attributes)) {
            return (float) $this->attributes['reviews_avg_rating'];
        }

        return (float) (
            DB::table('reviews')
            ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('product_variants.product_id', $this->id)
            ->where('reviews.is_visible', 1)
            ->avg('reviews.rating') ?? 0
        );
    }

    public function getReviewsCountAttribute(): int
    {
        if (array_key_exists('reviews_count', $this->attributes)) {
            return (int) $this->attributes['reviews_count'];
        }

        return (int) DB::table('reviews')
            ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('product_variants.product_id', $this->id)
            ->where('reviews.is_visible', 1)
            ->count('reviews.id');
    }
}