<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'code',
        'name',
        'type',              // product | order
        'discount_type',     // percent | fixed
        'discount_value',
        'min_order_value',
        'max_discount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    /* =====================================================
        RELATIONSHIPS
    ===================================================== */

    // Promotion → promotion_products
    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class, 'promotion_id');
    }

    // Promotion → products (qua bảng trung gian)
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_products',
            'promotion_id',
            'product_id'
        );
    }

    /* =====================================================
        SCOPES
    ===================================================== */

    // Khuyến mãi đang hiệu lực (dùng cho frontend / checkout)
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /* =====================================================
        BUSINESS LOGIC
    ===================================================== */

    // Kiểm tra khuyến mãi có hợp lệ để dùng không
    public function isValid(): bool
    {
        return $this->is_active
            && now()->between($this->start_date, $this->end_date)
            && (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

    /* =====================================================
        TIME STATUS (CHO ADMIN UI)
    ===================================================== */

    /**
     * upcoming | active | expiring | expired
     */
    public function getTimeStatusAttribute(): string
    {
        $now = Carbon::now();

        if ($this->start_date->gt($now)) {
            return 'upcoming';
        }

        if ($this->end_date->lt($now)) {
            return 'expired';
        }

        if ($now->diffInDays($this->end_date) <= 3) {
            return 'expiring';
        }

        return 'active';
    }

    /**
     * Số ngày còn lại (INT – không lẻ, không âm)
     */
    public function getDaysLeftAttribute(): int
    {
        $now = Carbon::now();

        if ($this->start_date->gt($now)) {
            return max(1, $now->diffInDays($this->start_date));
        }

        if ($this->end_date->lt($now)) {
            return 0;
        }

        return max(1, $now->diffInDays($this->end_date));
    }

    /**
     * Label hiển thị cho admin
     */
    public function getTimeStatusLabelAttribute(): string
    {
        return match ($this->time_status) {
            'upcoming' => "Sắp diễn ra ({$this->days_left} ngày)",
            'expiring' => "Sắp hết hạn ({$this->days_left} ngày)",
            'active'   => "Còn hạn ({$this->days_left} ngày)",
            'expired'  => "Đã hết hạn",
        };
    }

    /**
     * Màu badge Bootstrap
     */
    public function getTimeStatusColorAttribute(): string
    {
        return match ($this->time_status) {
            'upcoming' => 'info',
            'expiring' => 'warning',
            'active'   => 'success',
            'expired'  => 'dark',
        };
    }
    
}