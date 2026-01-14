<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',

        // ✅ CHỈ DÙNG TỰ NHẬP
        'attribute_name',   // VD: Màu sắc / Loại da / Công dụng
        'attribute_value',  // VD: Đỏ / Da dầu / Trị mụn

        'price',
        'original_price',
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

    /* ======================
        RELATIONS
    ====================== */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Ảnh riêng của biến thể
     */
    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    /* ======================
        HELPERS
    ====================== */

    /**
     * Tồn kho khả dụng
     */
    public function availableStock(): int
    {
        return max(0, $this->stock - $this->sold_quantity);
    }

    /**
     * Có còn bán được không
     */
    public function isInStock(): bool
    {
        return $this->availableStock() > 0 && $this->is_active;
    }

    /**
     * Chuỗi hiển thị biến thể
     * VD: Màu sắc: Đỏ
     */
    public function displayName(): string
    {
        return "{$this->attribute_name}: {$this->attribute_value}";
    }
}