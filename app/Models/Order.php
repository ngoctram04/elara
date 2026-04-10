<?php

namespace App\Models;

use App\Mail\OrderCompletedMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'voucher_discount',
        'birthday_discount',
        'shipping_fee',
        'shipping_cost',
        'total',
        'grand_total',
        'promotion_code',
        'status',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'note',
        'payment_method',
        'payment_status',
        'transaction_code',
        'paid_at',
        'cancel_reason',
        'cancelled_by',
        'cancelled_by_user_id',
        'delivered_at',
        'customer_confirmed',
        'received_at',
        'delivery_image',
        'cancelled_at',
    ];

    protected $casts = [
        'user_id'             => 'integer',
        'subtotal'            => 'decimal:2',
        'discount'            => 'decimal:2',
        'voucher_discount'    => 'decimal:2',
        'birthday_discount'   => 'decimal:2',
        'shipping_fee'        => 'decimal:2',
        'shipping_cost'       => 'decimal:2',
        'total'               => 'decimal:2',
        'grand_total'         => 'decimal:2',
        'status'              => 'integer',
        'payment_status'      => 'integer',
        'paid_at'             => 'datetime',
        'delivered_at'        => 'datetime',
        'received_at'         => 'datetime',
        'cancelled_at'        => 'datetime',
        'customer_confirmed'  => 'boolean',
        'cancelled_by_user_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS
    |--------------------------------------------------------------------------
    */
    public const STATUS_PENDING    = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_COMPLETED  = 3;
    public const STATUS_CANCELLED  = 4;
    public const STATUS_RETURNED   = 5;

    /*
    |--------------------------------------------------------------------------
    | PAYMENT STATUS
    |--------------------------------------------------------------------------
    */
    public const PAYMENT_UNPAID         = 0;
    public const PAYMENT_PAID           = 1;
    public const PAYMENT_FAILED         = 2;
    public const PAYMENT_REFUNDED       = 3;
    public const PAYMENT_REFUND_PENDING = 4;

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function refundRequest(): HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS - ORDER STATUS
    |--------------------------------------------------------------------------
    */
    public function getStatusNameAttribute(): string
    {
        if (
            (int) $this->status === self::STATUS_COMPLETED &&
            !(bool) $this->customer_confirmed
        ) {
            return 'Đã giao - chờ xác nhận';
        }

        return match ((int) $this->status) {
            self::STATUS_PENDING    => 'Đang xử lý',
            self::STATUS_PROCESSING => 'Đang giao',
            self::STATUS_COMPLETED  => 'Hoàn tất',
            self::STATUS_CANCELLED  => 'Đã huỷ',
            self::STATUS_RETURNED   => 'Trả hàng',
            default                 => 'Không xác định',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ((int) $this->status) {
            self::STATUS_PENDING    => 'warning',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED  => 'success',
            self::STATUS_CANCELLED  => 'danger',
            self::STATUS_RETURNED   => 'info',
            default                 => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS - PAYMENT
    |--------------------------------------------------------------------------
    */
    public function getPaymentMethodNameAttribute(): string
    {
        return match ($this->payment_method) {
            'cod'   => 'Thanh toán khi nhận hàng',
            'bank'  => 'Chuyển khoản',
            'vnpay' => 'VNPay',
            default => 'Không xác định',
        };
    }

    public function getPaymentStatusNameAttribute(): string
    {
        if ($this->payment_method === 'cod') {
            return (int) $this->status === self::STATUS_COMPLETED
                ? 'Đã thanh toán'
                : 'Chưa thanh toán';
        }

        return match ((int) $this->payment_status) {
            self::PAYMENT_PAID           => 'Đã thanh toán',
            self::PAYMENT_FAILED         => 'Thanh toán thất bại',
            self::PAYMENT_REFUNDED       => 'Đã hoàn tiền',
            self::PAYMENT_REFUND_PENDING => 'Chờ hoàn tiền',
            default                      => 'Chưa thanh toán',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        if ($this->payment_method === 'cod') {
            return (int) $this->status === self::STATUS_COMPLETED
                ? 'success'
                : 'secondary';
        }

        return match ((int) $this->payment_status) {
            self::PAYMENT_PAID           => 'success',
            self::PAYMENT_FAILED         => 'danger',
            self::PAYMENT_REFUNDED       => 'warning',
            self::PAYMENT_REFUND_PENDING => 'info',
            default                      => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS - ORDER LOGIC
    |--------------------------------------------------------------------------
    */
    public function canCancel(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return (int) $this->status === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return (int) $this->status === self::STATUS_COMPLETED
            && (bool) $this->customer_confirmed;
    }

    public function isWaitingCustomerConfirm(): bool
    {
        return (int) $this->status === self::STATUS_COMPLETED
            && !(bool) $this->customer_confirmed;
    }

    public function isDelivered(): bool
    {
        return (int) $this->status === self::STATUS_COMPLETED;
    }

    public function canMoveNext(): bool
    {
        return in_array((int) $this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ], true);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getCancelledByNameAttribute(): ?string
    {
        if (!$this->cancelled_by_user_id) {
            return null;
        }

        return $this->cancelledByUser->name ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS - DISCOUNT
    |--------------------------------------------------------------------------
    */
    public function getVoucherDiscountAttribute($value): float
    {
        if ((float) $value > 0) {
            return (float) $value;
        }

        if ((float) $this->discount > 0 && (float) $this->birthday_discount == 0) {
            return (float) $this->discount;
        }

        return 0;
    }

    public function getBirthdayDiscountAttribute($value): float
    {
        return (float) ($value ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
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

    public function scopeConfirmedCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->where('customer_confirmed', true);
    }

    public function scopeWaitingCustomerConfirm($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->where(function ($q) {
                $q->where('customer_confirmed', false)
                    ->orWhereNull('customer_confirmed');
            });
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

    /*
    |--------------------------------------------------------------------------
    | BUSINESS METHODS
    |--------------------------------------------------------------------------
    */
    public function refundVoucher(): void
    {
        if ($this->promotion_code) {
            Promotion::where('code', $this->promotion_code)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            // 1. Giao thành công -> gửi mail
            if (
                $order->isDirty('status') &&
                (int) $order->status === self::STATUS_COMPLETED
            ) {
                if (!$order->delivered_at) {
                    $order->delivered_at = now();
                }

                if (is_null($order->customer_confirmed)) {
                    $order->customer_confirmed = false;
                }

                $order->loadMissing('user');

                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)
                        ->send(new OrderCompletedMail($order));
                }
            }

            // 2. Huỷ đơn
            if (
                $order->isDirty('status') &&
                (int) $order->status === self::STATUS_CANCELLED
            ) {
                if (!$order->cancelled_at) {
                    $order->cancelled_at = now();
                }

                if (
                    $order->payment_method === 'vnpay' &&
                    (int) $order->payment_status === self::PAYMENT_PAID
                ) {
                    $order->payment_status = self::PAYMENT_REFUND_PENDING;
                }
            }

            // 3. COD giao xong = đã thanh toán
            if (
                $order->payment_method === 'cod' &&
                $order->isDirty('status') &&
                (int) $order->status === self::STATUS_COMPLETED
            ) {
                $order->payment_status = self::PAYMENT_PAID;

                if (!$order->paid_at) {
                    $order->paid_at = now();
                }
            }

            // 4. Khi payment_status chuyển paid mà chưa có paid_at
            if (
                $order->isDirty('payment_status') &&
                (int) $order->payment_status === self::PAYMENT_PAID &&
                !$order->paid_at
            ) {
                $order->paid_at = now();
            }

            // 5. Khi khách xác nhận nhận hàng
            if (
                $order->isDirty('customer_confirmed') &&
                (bool) $order->customer_confirmed === true &&
                !$order->received_at
            ) {
                $order->received_at = now();
            }
        });
    }

    public function canRequestRefund(): bool
    {
        if ((int) $this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        if (!(bool) $this->customer_confirmed) {
            return false;
        }

        if (!$this->received_at) {
            return false;
        }

        if ($this->refundRequest) {
            return false;
        }

        return now()->lte($this->received_at->copy()->addDays(3));
    }

    public function getRefundDeadlineAttribute()
    {
        return $this->received_at?->copy()->addDays(3);
    }
}