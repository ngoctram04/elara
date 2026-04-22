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


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

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