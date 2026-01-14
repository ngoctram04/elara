<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPromotion extends Model
{
    protected $table = 'order_promotions';

    protected $fillable = [
        'order_id',
        'promotion_code',
        'promotion_name',
        'discount_amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}