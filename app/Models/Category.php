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
        'image',
    ];


    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenSorted(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('name');
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildrenOnly($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function getTotalProductsAttribute(): int
    {
        if ($this->parent_id) {
            return $this->products_count
                ?? $this->products()->count();
        }

        return $this->children->sum(function ($child) {
            return $child->products_count
                ?? $child->products()->count();
        });
    }
}