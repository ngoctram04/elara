<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
    /**
     * Table name (khuyến nghị khai báo rõ)
     */
    protected $table = 'variant_images';

    /**
     * Mass assignment
     */
    protected $fillable = [
        'variant_id',
        'image_path',
        'is_main',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_main'    => 'boolean',
    ];

    /* =====================================================
        RELATIONS
    ===================================================== */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /* =====================================================
        ACCESSORS
    ===================================================== */

    /**
     * Full URL của ảnh
     * 👉 dùng: $variantImage->url
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->image_path, '/'));
    }

    /* =====================================================
        SCOPES
    ===================================================== */

    /**
     * Scope ảnh chính
     * 👉 VariantImage::main()->first()
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

}