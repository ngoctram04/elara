<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
    protected $fillable = [
        'variant_id',
        'image_path',
        'is_main',
        'sort_order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    /* ======================
        RELATIONS
    ====================== */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /* ======================
        HELPERS
    ====================== */

    // Lấy full URL ảnh
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    // Scope ảnh chính
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }
}