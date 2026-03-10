<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'variant_id',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id'
    ];

    protected $casts = [
        'variant_id' => 'integer',
        'quantity_change' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'reference_id' => 'integer'
    ];

    /**
     * Quan hệ biến thể sản phẩm
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Quan hệ sản phẩm (thông qua variant)
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id',
            'id',
            'variant_id',
            'product_id'
        );
    }
}