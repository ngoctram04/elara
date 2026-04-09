<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointReward extends Model
{
    protected $fillable = [
        'title',
        'points_required',
        'member_level',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_discount',
        'valid_days',
        'redeem_start_at',
        'redeem_end_at',
        'is_active'
    ];

    protected $casts = [
        'redeem_start_at' => 'datetime',
        'redeem_end_at' => 'datetime',
    ];
}