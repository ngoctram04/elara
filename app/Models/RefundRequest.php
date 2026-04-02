<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'status',
        'admin_note',
        'loss_amount',
        'refund_total',
        'restock_total_qty',
        'damaged_total_qty',
    ];

    protected $casts = [
        'loss_amount'       => 'float',
        'refund_total'      => 'float',
        'restock_total_qty' => 'integer',
        'damaged_total_qty' => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REFUNDED = 'refunded';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(RefundMedia::class, 'refund_request_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            OrderItem::class,
            'refund_request_items',
            'refund_request_id',
            'order_item_id'
        )->withPivot([
            'variant_id',
            'quantity',
            'reason',
            'condition_status',
            'is_sealed',
            'restockable',
            'returned_to_stock',
            'refund_amount',
            'unit_cost',
            'loss_amount',
            'note',
        ])->withTimestamps();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Đang chờ xử lý',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            self::STATUS_REFUNDED => 'Đã hoàn tiền',
            default               => 'Không xác định',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'bg-warning',
            self::STATUS_APPROVED => 'bg-primary',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_REFUNDED => 'bg-success',
            default               => 'bg-secondary',
        };
    }
}