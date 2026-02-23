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
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'note',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'total' => 'integer',
        'status' => 'integer',
        'payment_status' => 'integer',
    ];

    /*
    |--------------------------------
    | STATUS
    |--------------------------------
    */
    const STATUS_PENDING    = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETED  = 3;
    const STATUS_CANCELLED  = 4;

    /*
    |--------------------------------
    | PAYMENT
    |--------------------------------
    */
    const PAYMENT_UNPAID = 0;
    const PAYMENT_PAID   = 1;

    /*
    |--------------------------------
    | RELATIONSHIPS
    |--------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function promotions()
    {
        return $this->hasMany(OrderPromotion::class, 'order_id');
    }

    /*
    |--------------------------------
    | ACCESSORS
    |--------------------------------
    */
    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang giao',
            self::STATUS_COMPLETED  => 'Hoàn thành',
            self::STATUS_CANCELLED  => 'Đã huỷ',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentMethodNameAttribute()
    {
        return match ($this->payment_method) {
            'cod'   => 'Thanh toán khi nhận hàng',
            'bank'  => 'Chuyển khoản',
            'vnpay' => 'VNPay',
            default => 'Không xác định',
        };
    }

    public function getPaymentStatusNameAttribute()
    {
        return $this->payment_status == self::PAYMENT_PAID
            ? 'Đã thanh toán'
            : 'Chưa thanh toán';
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    /*
    |--------------------------------
    | SCOPES
    |--------------------------------
    */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
}