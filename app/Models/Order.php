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
        'transaction_code', // dùng cho VNPay (nếu có)
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
    | ORDER STATUS
    |--------------------------------
    */
    const STATUS_PENDING    = 1; // Chờ xử lý
    const STATUS_PROCESSING = 2; // Đang giao
    const STATUS_COMPLETED  = 3; // Hoàn thành
    const STATUS_CANCELLED  = 4; // Đã huỷ

    /*
    |--------------------------------
    | PAYMENT STATUS
    |--------------------------------
    */
    const PAYMENT_UNPAID = 0; // Chưa thanh toán
    const PAYMENT_PAID   = 1; // Đã thanh toán
    const PAYMENT_FAILED = 2; // Thanh toán thất bại

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
    | ACCESSORS - ORDER STATUS
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
            self::STATUS_PENDING    => 'warning',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED  => 'success',
            self::STATUS_CANCELLED  => 'danger',
            default => 'secondary',
        };
    }

    /*
    |--------------------------------
    | ACCESSORS - PAYMENT
    |--------------------------------
    */
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
        return match ($this->payment_status) {
            self::PAYMENT_PAID   => 'Đã thanh toán',
            self::PAYMENT_FAILED => 'Thanh toán thất bại',
            default              => 'Chưa thanh toán',
        };
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID   => 'success',
            self::PAYMENT_FAILED => 'danger',
            default              => 'secondary',
        };
    }

    /*
    |--------------------------------
    | HELPERS
    |--------------------------------
    */

    // Đã thanh toán chưa?
    public function isPaid()
    {
        return $this->payment_status == self::PAYMENT_PAID;
    }

    // Thanh toán thất bại?
    public function isPaymentFailed()
    {
        return $this->payment_status == self::PAYMENT_FAILED;
    }

    // Chưa thanh toán?
    public function isUnpaid()
    {
        return $this->payment_status == self::PAYMENT_UNPAID;
    }

    // Tổng số lượng sản phẩm
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

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_UNPAID);
    }
}