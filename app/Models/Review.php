<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'order_item_id',
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

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ReviewMedia::class, 'review_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewMedia::class, 'review_id')
            ->where('file_type', 'image');
    }

    public function video(): HasOne
    {
        return $this->hasOne(ReviewMedia::class, 'review_id')
            ->where('file_type', 'video');
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->rating >= 4;
    }

    public function getIsNegativeAttribute(): bool
    {
        return $this->rating <= 2;
    }

    public function getIsNeutralAttribute(): bool
    {
        return $this->rating == 3;
    }

    public function getIsRepliedAttribute(): bool
    {
        return !empty($this->admin_reply);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_visible ? 'Hiển thị' : 'Đã ẩn';
    }

    public function getSentimentLabelAttribute(): string
    {
        if ($this->is_negative) {
            return 'Tiêu cực';
        }

        if ($this->is_neutral) {
            return 'Trung lập';
        }

        return 'Tích cực';
    }

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
    public function getUserAttribute()
    {
        return $this->orderItem?->order?->user;
    }

    public function getVariantAttribute()
    {
        return $this->orderItem?->variant;
    }
}