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
        'comment',
        'is_visible',
        'admin_reply',
        'replied_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_visible' => 'boolean',
        'replied_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function media()
    {
        return $this->hasMany(ReviewMedia::class, 'review_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Media Helpers
    |--------------------------------------------------------------------------
    */

    public function images()
    {
        return $this->hasMany(ReviewMedia::class, 'review_id')
            ->where('file_type', 'image');
    }

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

    public function getIsPositiveAttribute()
    {
        return $this->rating >= 4;
    }

    public function getIsNegativeAttribute()
    {
        return $this->rating <= 2;
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Helpers
    |--------------------------------------------------------------------------
    */

    public function getIsRepliedAttribute()
    {
        return !empty($this->admin_reply);
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_visible ? 'Hiển thị' : 'Đã ẩn';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    public function scopePendingReply($query)
    {
        return $query->whereNull('admin_reply');
    }

    public function scopeReplied($query)
    {
        return $query->whereNotNull('admin_reply');
    }
}