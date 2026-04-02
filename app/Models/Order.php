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
    public const STATUS_COMPLETED  = 3; // Đã giao
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

    /**
     * Người đặt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Người huỷ đơn
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    /**
     * Sản phẩm trong đơn
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Khuyến mãi áp dụng
     */


    /**
     * Yêu cầu hoàn tiền
     */
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
            self::STATUS_RETURNED   => 'Đổi trả',
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

    /**
     * Có thể huỷ đơn không
     */
    public function canCancel(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    /**
     * Đơn đã huỷ chưa
     */
    public function isCancelled(): bool
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    /**
     * Đơn đã hoàn tất chưa
     * Chỉ tính hoàn tất khi khách đã xác nhận nhận hàng
     */
    public function isCompleted(): bool
    {
        return $this->status == self::STATUS_COMPLETED && $this->customer_confirmed;
    }

    /**
     * Có thể chuyển trạng thái tiếp không (Admin)
     */
    public function canMoveNext(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Tổng số lượng sản phẩm trong đơn
     */
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Tên người huỷ
     */
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

    /**
     * Nếu đơn cũ chưa tách, coi toàn bộ discount là voucher
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
            |--------------------------------------------------------------------------
            | 1. GIAO THÀNH CÔNG → GỬI MAIL HOÀN TẤT
            |--------------------------------------------------------------------------
            */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
            ) {
                $order->loadMissing('user');

                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)
                        ->send(new OrderCompletedMail($order));
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. HUỶ ĐƠN → HOÀN KHO + TRẢ LẠI KHUYẾN MÃI
            |--------------------------------------------------------------------------
            */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_CANCELLED
            ) {
                if (!$order->cancelled_at) {
                    $order->cancelled_at = now();
                }

                $order->loadMissing('items.variant', 'user');

                // Hoàn tồn tổng cho variant
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock_quantity', $item->quantity);
                    }
                }

                // Trả lại voucher
                $order->refundVoucher();

                // Trả lại ưu đãi sinh nhật
                if ($order->birthday_discount > 0 && $order->user) {
                    $order->user->birthday_discount_year = null;
                    $order->user->save();
                }

                // Hoàn tiền VNPAY
                if (
                    $order->payment_method === 'vnpay' &&
                    $order->payment_status == self::PAYMENT_PAID
                ) {
                    $order->payment_status = self::PAYMENT_REFUNDED;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. ĐỔI TRẢ / HOÀN TIỀN
            | Không tự hoàn kho ở đây.
            | Việc hoàn kho / hoàn lô sẽ do RefundController quyết định
            | dựa trên tình trạng hàng trả:
            | - sealed => hoàn kho + hoàn lô
            | - broken => không hoàn kho, ghi nhận hao hụt
            |--------------------------------------------------------------------------
            */
            if (
                $order->isDirty('status') &&
                $order->getOriginal('status') != self::STATUS_RETURNED &&
                $order->status == self::STATUS_RETURNED
            ) {
                $order->loadMissing('items.variant', 'user');

                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->decrement('sold_quantity', $item->quantity);

                        if ($item->variant->sold_quantity < 0) {
                            $item->variant->sold_quantity = 0;
                            $item->variant->save();
                        }
                    }
                }

                /*
                ---------------------------------------
                TRỪ ĐIỂM THÀNH VIÊN
                ---------------------------------------
                */
                if ($order->user) {
                    $points = floor($order->grand_total / 1000);

                    $newPoints = max(0, $order->user->loyalty_points - $points);

                    $order->user->loyalty_points = $newPoints;
                    $order->user->total_spent = max(
                        0,
                        $order->user->total_spent - $order->grand_total
                    );
                    $order->user->save();

                    \App\Models\UserPointHistory::create([
                        'user_id'     => $order->user->id,
                        'points'      => -$points,
                        'type'        => 'refund',
                        'description' => 'Trừ điểm do trả hàng đơn #' . $order->id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 4. COD → Khi giao xong = đã thanh toán
            |--------------------------------------------------------------------------
            */
            if (
                $order->payment_method === 'cod' &&
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
            ) {
                $order->payment_status = self::PAYMENT_PAID;
            }
        });
    }
}