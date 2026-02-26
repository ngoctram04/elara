<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'product_id',
        'variant_id',

        'rating',

        'quality',
        'effectiveness',
        'fragrance',
        'texture',
        'packaging',

        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'quality' => 'integer',
        'effectiveness' => 'integer',
        'fragrance' => 'integer',
        'texture' => 'integer',
        'packaging' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Người đánh giá
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Order item (quan trọng nhất)
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    // Sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Variant
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Media (ảnh/video)
    public function media()
    {
        return $this->hasMany(ReviewMedia::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Lấy danh sách ảnh
    public function images()
    {
        return $this->media()->where('file_type', 'image');
    }

    // Lấy video (1 cái)
    public function video()
    {
        return $this->media()->where('file_type', 'video')->first();
    }

    // Trung bình đánh giá chi tiết
    public function getDetailAverageAttribute()
    {
        $fields = [
            $this->quality,
            $this->effectiveness,
            $this->fragrance,
            $this->texture,
            $this->packaging
        ];

        $valid = array_filter($fields);

        if (count($valid) == 0) {
            return null;
        }

        return round(array_sum($valid) / count($valid), 1);
    }

}