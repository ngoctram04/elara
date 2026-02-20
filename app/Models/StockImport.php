<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockImport extends Model
{
    protected $table = 'stock_imports';

    protected $fillable = [
        'variant_id',
        'quantity',
        'cost_price',
        'expiry_date' // thêm field này
    ];

    // Quan trọng: để format ngày bằng Carbon trong Blade
    protected $casts = [
        'expiry_date' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /* =======================
        RELATIONSHIP
    ======================= */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // tiện dùng trong history
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id',        // Foreign key trên product_variants
            'id',        // Foreign key trên products
            'variant_id', // Local key trên stock_imports
            'product_id' // Local key trên product_variants
        );
    }
}