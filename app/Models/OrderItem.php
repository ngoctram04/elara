<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'variant_id',
        'price',
        'cost_price',
        'quantity',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Thuộc đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Thuộc biến thể sản phẩm
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Lấy sản phẩm thông qua variant
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id',          // Foreign key trên product_variants
            'id',          // Foreign key trên products
            'variant_id',  // Local key trên order_items
            'product_id'   // Local key trên product_variants
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Tổng tiền của item (không lưu DB)
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }
    public function review()
    {
        return $this->hasOne(Review::class);
    }
    public function batches()
    {
        return $this->hasMany(\App\Models\OrderItemBatch::class, 'order_item_id');
    }
}