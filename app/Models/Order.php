<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCompletedMail;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'subtotal'           => 'integer',
        'discount'           => 'integer',
        'voucher_discount'   => 'integer',
        'birthday_discount'  => 'integer',
        'shipping_fee'       => 'integer',
        'shipping_cost'      => 'integer',
        'total'              => 'integer',
        'grand_total'        => 'integer',
        'status'             => 'integer',
        'payment_status'     => 'integer',
        'delivered_at'       => 'datetime',
        'cancelled_at'       => 'datetime',
        'customer_confirmed' => 'boolean',
        'received_at'        => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS
    |--------------------------------------------------------------------------
    */
    public const STATUS_PENDING    = 1; // Đang xử lý
    public const STATUS_PROCESSING = 2; // Đang giao
    public const STATUS_COMPLETED  = 3; // Đã giao / hoàn tất
    public const STATUS_CANCELLED  = 4; // Đã huỷ
    public const STATUS_RETURNED   = 5; // Đổi trả / hoàn tiền

    /*
    |--------------------------------------------------------------------------
    | PAYMENT STATUS
    |--------------------------------------------------------------------------
    */
    public const PAYMENT_UNPAID   = 0;
    public const PAYMENT_PAID     = 1;
    public const PAYMENT_FAILED   = 2;
    public const PAYMENT_REFUNDED = 3;

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
        if ($this->status == self::STATUS_COMPLETED && !$this->customer_confirmed) {
            return 'Đã giao - chờ xác nhận';
        }

        return match ($this->status) {
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
        return match ($this->status) {
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
        if ($this->payment_status == self::PAYMENT_REFUNDED) {
            return 'Đã hoàn tiền';
        }

        if ($this->payment_method === 'cod') {
            return $this->status == self::STATUS_COMPLETED
                ? 'Đã thanh toán'
                : 'Chưa thanh toán';
        }

        return match ($this->payment_status) {
            self::PAYMENT_PAID     => 'Đã thanh toán',
            self::PAYMENT_FAILED   => 'Thanh toán thất bại',
            self::PAYMENT_REFUNDED => 'Đã hoàn tiền',
            default                => 'Chưa thanh toán',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        if ($this->payment_status == self::PAYMENT_REFUNDED) {
            return 'warning';
        }

        if ($this->payment_method === 'cod') {
            return $this->status == self::STATUS_COMPLETED
                ? 'success'
                : 'secondary';
        }

        return match ($this->payment_status) {
            self::PAYMENT_PAID     => 'success',
            self::PAYMENT_FAILED   => 'danger',
            self::PAYMENT_REFUNDED => 'warning',
            default                => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS - ORDER LOGIC
    |--------------------------------------------------------------------------
    */
    public function canCancel(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status == self::STATUS_COMPLETED && $this->customer_confirmed;
    }

    public function isWaitingCustomerConfirm(): bool
    {
        return $this->status == self::STATUS_COMPLETED && !$this->customer_confirmed;
    }

    public function isDelivered(): bool
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function canMoveNext(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
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
    public function getVoucherDiscountAttribute($value): int
    {
        if ((int) $value > 0) {
            return (int) $value;
        }

        if ((int) $this->discount > 0 && (int) $this->birthday_discount == 0) {
            return (int) $this->discount;
        }

        return 0;
    }

    public function getBirthdayDiscountAttribute($value): int
    {
        return (int) ($value ?? 0);
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
        static::updating(function ($order) {
            /*
            |------------------------------------------------------------------
            | 1. GIAO THÀNH CÔNG -> GỬI MAIL
            | status = COMPLETED nghĩa là đơn đã giao thành công
            |------------------------------------------------------------------
            */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
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

            /*
            |------------------------------------------------------------------
            | 2. HUỶ ĐƠN
            |------------------------------------------------------------------
            */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_CANCELLED
            ) {
                if (!$order->cancelled_at) {
                    $order->cancelled_at = now();
                }

                if (
                    $order->payment_method === 'vnpay' &&
                    $order->payment_status == self::PAYMENT_PAID
                ) {
                    $order->payment_status = self::PAYMENT_REFUNDED;
                }
            }

            /*
            |------------------------------------------------------------------
            | 3. COD -> Khi giao xong = đã thanh toán
            |------------------------------------------------------------------
            */
            if (
                $order->payment_method === 'cod' &&
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
            ) {
                $order->payment_status = self::PAYMENT_PAID;
            }

            /*
            |------------------------------------------------------------------
            | 4. Khi khách xác nhận nhận hàng mà chưa có received_at
            |------------------------------------------------------------------
            */
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
        if ($this->status != self::STATUS_COMPLETED) {
            return false;
        }

        if (!(bool) $this->customer_confirmed) {
            return false;
        }

        if (!$this->received_at) {
            return false;
        }

        // đã từng gửi hoàn trả rồi thì ẩn luôn
        if ($this->refundRequest) {
            return false;
        }

        // quá 3 ngày thì ẩn luôn
        return now()->lte($this->received_at->copy()->addDays(3));
    }
    public function getRefundDeadlineAttribute()
    {
        return $this->received_at?->copy()->addDays(3);
    }
}