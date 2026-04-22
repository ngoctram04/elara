<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\RefundRequest;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'date_of_birth',
        'gender',
        'role',
        'is_active',
        'blocked_reason',
        'locked_until',
        'loyalty_points',
        'total_spent',
        'yearly_spent',
        'member_level',
        'membership_year',
        'birthday_discount_year',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'      => 'datetime',
        'password'               => 'hashed',
        'is_active'              => 'boolean',
        'date_of_birth'          => 'date',
        'birthday_discount_year' => 'integer',
        'loyalty_points'         => 'integer',
        'total_spent'            => 'float',
        'yearly_spent'           => 'float',
        'membership_year'        => 'integer',
        'locked_until'           => 'datetime',
    ];

  

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function pointHistories()
    {
        return $this->hasMany(UserPointHistory::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function conversations()
    {
        return $this->hasMany(ChatConversation::class, 'user_id');
    }

    public function adminConversations()
    {
        return $this->hasMany(ChatConversation::class, 'admin_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }


    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        return asset('images/avatar-default.png');
    }


    public function getYearlyRefundProductTotalAttribute(): float
    {
        return (float) DB::table('refund_requests')
            ->join('refund_request_items', 'refund_requests.id', '=', 'refund_request_items.refund_request_id')
            ->join('order_items', 'refund_request_items.order_item_id', '=', 'order_items.id')
            ->where('refund_requests.user_id', $this->id)
            ->where('refund_requests.status', RefundRequest::STATUS_REFUNDED)
            ->whereYear('refund_requests.updated_at', now()->year)
            ->sum(DB::raw('COALESCE(order_items.price, 0) * COALESCE(refund_request_items.quantity, 0)'));
    }


    public function getRefundProductTotalAttribute(): float
    {
        return (float) DB::table('refund_requests')
            ->join('refund_request_items', 'refund_requests.id', '=', 'refund_request_items.refund_request_id')
            ->join('order_items', 'refund_request_items.order_item_id', '=', 'order_items.id')
            ->where('refund_requests.user_id', $this->id)
            ->where('refund_requests.status', RefundRequest::STATUS_REFUNDED)
            ->sum(DB::raw('COALESCE(order_items.price, 0) * COALESCE(refund_request_items.quantity, 0)'));
    }


    public function getYearlySpentCalculatedAttribute(): float
    {
        $completedTotal = (float) $this->orders()
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_RETURNED])
            ->whereNotNull('delivered_at')
            ->whereYear('delivered_at', now()->year)
            ->sum('total');

        $refundedProductTotal = $this->yearly_refund_product_total;

        return max(0, $completedTotal - $refundedProductTotal);
    }


    public function getTotalSpentCalculatedAttribute(): float
    {
        $completedTotal = (float) $this->orders()
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_RETURNED])
            ->whereNotNull('delivered_at')
            ->sum('total');

        $refundedProductTotal = $this->refund_product_total;

        return max(0, $completedTotal - $refundedProductTotal);
    }

    public function getCalculatedMemberLevelAttribute(): string
    {
        $spent = $this->yearly_spent_calculated;

        if ($spent >= 10000000) {
            return 'diamond';
        }

        if ($spent >= 3000000) {
            return 'gold';
        }

        if ($spent >= 1000000) {
            return 'silver';
        }

        return 'bronze';
    }

    public function getCalculatedMemberLevelNameAttribute(): string
    {
        return match ($this->calculated_member_level) {
            'diamond' => 'Kim Cương',
            'gold'    => 'Vàng',
            'silver'  => 'Bạc',
            default   => 'Đồng',
        };
    }



    public function calculateYearlySpent()
    {
        return $this->yearly_spent_calculated;
    }

    public function calculateTotalSpent()
    {
        return $this->total_spent_calculated;
    }

    public function calculateMemberLevel()
    {
        return $this->calculated_member_level;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isActive()
    {
        return (bool) $this->is_active;
    }

    public function isTemporarilyLocked()
    {
        return !is_null($this->locked_until) && now()->lt($this->locked_until);
    }

    public function isBlocked()
    {
        return !$this->is_active || $this->isTemporarilyLocked();
    }

    public function lockForMinutes($minutes, $reason = null)
    {
        $this->forceFill([
            'locked_until'   => now()->addMinutes($minutes),
            'blocked_reason' => $reason,
            'is_active'      => false,
        ])->save();
    }

    public function unlockAccount()
    {
        $this->forceFill([
            'locked_until'   => null,
            'blocked_reason' => null,
            'is_active'      => true,
        ])->save();
    }

    public function hasUsedBirthdayDiscount()
    {
        return (int) $this->birthday_discount_year === (int) now()->year;
    }

    public function markBirthdayDiscountUsed()
    {
        $this->forceFill([
            'birthday_discount_year' => now()->year,
        ])->save();
    }


    public function updateMemberLevel()
    {
        $spent = (float) ($this->yearly_spent ?? 0);

        $level = 'bronze';

        if ($spent >= 10000000) {
            $level = 'diamond';
        } elseif ($spent >= 3000000) {
            $level = 'gold';
        } elseif ($spent >= 1000000) {
            $level = 'silver';
        }

        $this->forceFill([
            'member_level'    => $level,
            'membership_year' => now()->year,
        ])->save();
    }

  
    public function refreshMembershipData()
    {
        $yearlySpent = $this->yearly_spent_calculated;
        $totalSpent  = $this->total_spent_calculated;

        $level = 'bronze';

        if ($yearlySpent >= 10000000) {
            $level = 'diamond';
        } elseif ($yearlySpent >= 3000000) {
            $level = 'gold';
        } elseif ($yearlySpent >= 1000000) {
            $level = 'silver';
        }

        $this->forceFill([
            'yearly_spent'    => $yearlySpent,
            'total_spent'     => $totalSpent,
            'member_level'    => $level,
            'membership_year' => now()->year,
        ])->save();
    }
}