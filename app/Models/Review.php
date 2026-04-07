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
        'is_flagged',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_visible' => 'boolean',
        'is_flagged' => 'boolean',
        'replied_at' => 'datetime',
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
    | Rating / Status Helpers
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

    public function getIsNeutralAttribute()
    {
        return $this->rating == 3;
    }

    public function getIsRepliedAttribute()
    {
        return !empty($this->admin_reply);
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_visible ? 'Hiển thị' : 'Đã ẩn';
    }

    public function getSentimentLabelAttribute()
    {
        if ($this->is_negative) {
            return 'Tiêu cực';
        }

        if ($this->is_neutral) {
            return 'Trung lập';
        }

        return 'Tích cực';
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

    public function scopeHidden($query)
    {
        return $query->where('is_visible', 0);
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', 1);
    }

    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
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