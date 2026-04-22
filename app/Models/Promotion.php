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
        'type',              
        'discount_type',  
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


    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class, 'promotion_id');
    }


    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_products',
            'promotion_id',
            'product_id'
        );
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

  
    public function isValid(): bool
    {
        return $this->is_active
            && now()->between($this->start_date, $this->end_date)
            && (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

  
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

 
    public function getTimeStatusLabelAttribute(): string
    {
        return match ($this->time_status) {
            'upcoming' => "Sắp diễn ra ({$this->days_left} ngày)",
            'expiring' => "Sắp hết hạn ({$this->days_left} ngày)",
            'active'   => "Còn hạn ({$this->days_left} ngày)",
            'expired'  => "Đã hết hạn",
        };
    }

    public function getTimeStatusColorAttribute(): string
    {
        return match ($this->time_status) {
            'upcoming' => 'info',
            'expiring' => 'warning',
            'active'   => 'success',
            'expired'  => 'dark',
        };
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id');
    }
}