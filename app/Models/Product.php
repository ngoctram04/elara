<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /* ======================
        HELPERS (CHO VIEW)
    ====================== */

    // Có biến thể không?
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    // Giá thấp nhất
    public function minPrice(): float
    {
        return (float) ($this->variants()->min('price') ?? 0);
    }

    // Giá cao nhất
    public function maxPrice(): float
    {
        return (float) ($this->variants()->max('price') ?? 0);
    }

    // Tổng tồn kho
    public function totalStock(): int
    {
        return (int) $this->variants()->sum('stock');
    }

    // Tổng đã bán
    public function totalSold(): int
    {
        return (int) $this->variants()->sum('sold_quantity');
    }

    // Số biến thể
    public function variantsCount(): int
    {
        return $this->variants()->count();
    }
}