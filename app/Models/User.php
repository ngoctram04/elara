<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

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

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

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

    public function getYearlySpentCalculatedAttribute()
    {
        return $this->calculateYearlySpent();
    }

    public function getTotalSpentCalculatedAttribute()
    {
        return $this->calculateTotalSpent();
    }

    public function getCalculatedMemberLevelAttribute()
    {
        return $this->calculateMemberLevel();
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATION METHODS
    |--------------------------------------------------------------------------
    */

    public function calculateYearlySpent()
    {
        return (float) $this->orders()
            ->where('status', 3)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');
    }

    public function calculateTotalSpent()
    {
        return (float) $this->orders()
            ->where('status', 3)
            ->sum('grand_total');
    }

    public function calculateMemberLevel()
    {
        $spent = (float) $this->calculateYearlySpent();

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

    /*
    |--------------------------------------------------------------------------
    | ROLE / STATUS
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | BIRTHDAY DISCOUNT
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | MEMBERSHIP
    |--------------------------------------------------------------------------
    */

    public function updateMemberLevel()
    {
        $level = $this->calculateMemberLevel();

        $this->forceFill([
            'member_level'    => $level,
            'membership_year' => now()->year,
        ])->save();
    }

    public function refreshMembershipData()
    {
        $yearlySpent = $this->calculateYearlySpent();
        $totalSpent  = $this->calculateTotalSpent();
        $level       = $this->calculateMemberLevel();

        $this->forceFill([
            'yearly_spent'    => $yearlySpent,
            'total_spent'     => $totalSpent,
            'member_level'    => $level,
            'membership_year' => now()->year,
        ])->save();
    }
}