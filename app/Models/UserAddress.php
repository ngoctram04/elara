<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'user_addresses';
    protected $primaryKey = 'id';
    public $timestamps = true;

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
    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }


    public function getFullAddressAttribute()
    {
        return "{$this->address_detail}, {$this->ward}, {$this->district}, {$this->province}";
    }



    protected static function booted()
    {
        static::creating(function ($address) {
            if ($address->is_default) {
                self::where('user_id', $address->user_id)
                    ->update(['is_default' => false]);
            }

            if (!self::where('user_id', $address->user_id)->exists()) {
                $address->is_default = true;
            }
        });
    }
}