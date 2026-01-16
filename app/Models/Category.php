<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    /**
     * ======================
     * QUAN HỆ CHA / CON
     * ======================
     */

    // Danh mục cha
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Danh mục con (menu / sidebar)
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('name');
    }

    /**
     * ======================
     * SẢN PHẨM
     * ======================
     */

    // Sản phẩm trực tiếp thuộc danh mục
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * ======================
     * SCOPE
     * ======================
     */

    // Chỉ lấy danh mục cha
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Chỉ lấy danh mục con
    public function scopeChildrenOnly($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * ======================
     * ACCESSOR
     * ======================
     */

    /**
     * Tổng số sản phẩm
     * - Danh mục con: đếm trực tiếp
     * - Danh mục cha: tổng sản phẩm các danh mục con
     */
    public function getTotalProductsAttribute(): int
    {
        // Danh mục con
        if ($this->parent_id) {
            return $this->products_count
                ?? $this->products()->count();
        }

        // Danh mục cha
        return $this->children->sum(function ($child) {
            return $child->products_count
                ?? $child->products()->count();
        });
    }
}