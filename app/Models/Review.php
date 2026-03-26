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

        'is_visible',
        'admin_reply',
        'replied_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'quality' => 'integer',
        'effectiveness' => 'integer',
        'fragrance' => 'integer',
        'texture' => 'integer',
        'packaging' => 'integer',

        'is_visible' => 'boolean',
        'replied_at' => 'datetime'
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

    // Order item
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
        return $this->hasMany(ReviewMedia::class, 'review_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Media Helpers
    |--------------------------------------------------------------------------
    */

    // Ảnh
    public function images()
    {
        return $this->hasMany(ReviewMedia::class, 'review_id')
        ->where('file_type', 'image');
    }

    // Video (1 video)
    public function video()
    {
        return $this->hasOne(ReviewMedia::class, 'review_id')
        ->where('file_type', 'video');
    }


    /*
    |--------------------------------------------------------------------------
    | Rating Helpers
    |--------------------------------------------------------------------------
    */

    // Trung bình rating chi tiết
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


    // Review tốt
    public function getIsPositiveAttribute()
    {
        return $this->rating >= 4;
    }

    // Review xấu
    public function getIsNegativeAttribute()
    {
        return $this->rating <= 2;
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Helpers
    |--------------------------------------------------------------------------
    */

    // Đã trả lời
    public function getIsRepliedAttribute()
    {
        return !empty($this->admin_reply);
    }

    // Trạng thái hiển thị
    public function getStatusLabelAttribute()
    {
        return $this->is_visible ? 'Hiển thị' : 'Đã ẩn';
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    // Review hiển thị
    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    // Review chưa trả lời
    public function scopePendingReply($query)
    {
        return $query->whereNull('admin_reply');
    }

    // Review đã trả lời
    public function scopeReplied($query)
    {
        return $query->whereNotNull('admin_reply');
    }
    
}