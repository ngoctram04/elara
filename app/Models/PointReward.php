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
        'is_active'
    ];
}