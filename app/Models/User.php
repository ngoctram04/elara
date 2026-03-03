<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

// Import Models
use App\Models\Review;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\UserAddress;
use App\Models\UserPointHistory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
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

        // Loyalty System
        'loyalty_points',
        'total_spent',
        'member_level',
        'birthday_discount_year',

        'email_verified_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at'      => 'datetime',
        'password'               => 'hashed',
        'is_active'              => 'boolean',
        'date_of_birth'          => 'date',
        'birthday_discount_year' => 'integer',
        'loyalty_points'         => 'integer',
        'total_spent'            => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Đơn hàng
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Giỏ hàng
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Wishlist
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Sổ địa chỉ
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // 🔥 LỊCH SỬ ĐIỂM (FIX LỖI Undefined method pointHistories)
    public function pointHistories()
    {
        return $this->hasMany(UserPointHistory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    // Tuổi
    public function getAgeAttribute()
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }

    // Avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        return asset('images/avatar-default.png');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // Kiểm tra đã dùng ưu đãi sinh nhật năm nay chưa
    public function hasUsedBirthdayDiscount()
    {
        return $this->birthday_discount_year == now()->year;
    }

    // Đánh dấu đã dùng ưu đãi sinh nhật
    public function markBirthdayDiscountUsed()
    {
        $this->birthday_discount_year = now()->year;
        $this->save();
    }
    public function getMemberLevelAttribute()
    {
        $points = $this->loyalty_points;

        if ($points >= 20000) return 'diamond';
        if ($points >= 5000) return 'gold';
        if ($points >= 1000) return 'silver';
        return 'bronze';
    }
}