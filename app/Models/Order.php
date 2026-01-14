<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'total',
        'status',
    ];

    /* ================= RELATIONSHIPS ================= */

    public function promotions()
    {
        return $this->hasMany(OrderPromotion::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotion()
    {
        return $this->hasOne(OrderPromotion::class);
    }

}