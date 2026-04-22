<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
 
    protected $table = 'variant_images';


    protected $fillable = [
        'variant_id',
        'image_path',
        'is_main',
    ];


    protected $casts = [
        'is_main'    => 'boolean',
    ];



    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->image_path, '/'));
    }


    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

}