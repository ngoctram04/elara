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

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function hasUsedBirthdayDiscount()
    {
        return $this->birthday_discount_year == now()->year;
    }

    public function markBirthdayDiscountUsed()
    {
        $this->birthday_discount_year = now()->year;
        $this->save();
    }

    public function getMemberLevelAttribute()
    {
        $spent = (float) ($this->yearly_spent ?? 0);

        if ($spent >= 10000000) return 'platinum';
        if ($spent >= 3000000) return 'gold';
        if ($spent >= 1000000) return 'silver';
        return 'bronze';
    }

    public function updateMemberLevel()
    {
        $spent = (float) ($this->yearly_spent ?? 0);

        if ($spent >= 10000000) {
            $level = 'platinum';
        } elseif ($spent >= 3000000) {
            $level = 'gold';
        } elseif ($spent >= 1000000) {
            $level = 'silver';
        } else {
            $level = 'bronze';
        }

        $this->forceFill([
            'member_level' => $level,
            'membership_year' => now()->year,
        ])->save();
    }
}