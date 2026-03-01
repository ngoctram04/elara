<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAddress extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table & Primary Key
    |--------------------------------------------------------------------------
    */
    protected $table = 'user_addresses';
    protected $primaryKey = 'id';
    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'receiver_name',
        'phone',
        'province',
        'district',
        'ward',
        'address_detail',
        'is_default'
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    // Địa chỉ mặc định
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    // Địa chỉ đầy đủ
    public function getFullAddressAttribute()
    {
        return "{$this->address_detail}, {$this->ward}, {$this->district}, {$this->province}";
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTS (QUAN TRỌNG)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($address) {
            // Nếu là địa chỉ mặc định → reset các địa chỉ khác
            if ($address->is_default) {
                self::where('user_id', $address->user_id)
                    ->update(['is_default' => false]);
            }

            // Nếu user chưa có địa chỉ nào → auto mặc định
            if (!self::where('user_id', $address->user_id)->exists()) {
                $address->is_default = true;
            }
        });
    }
}