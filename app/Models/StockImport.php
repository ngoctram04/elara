<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockImport extends Model
{
    protected $table = 'stock_imports';

    protected $fillable = [
        'variant_id',
        'quantity',
        'remaining_quantity',   // 🔥 thêm
        'imported_quantity',    // 🔥 thêm
        'expired_quantity',     // 🔥 thêm

        'cost_price',
        'manufacture_date',
        'expiry_date',

        'expired_at',           // 🔥 thêm

        'code',
        'supplier',
        'note',
        'created_by'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date'      => 'date',
        'expired_at'       => 'datetime', // 🔥 thêm

        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |-------------------------------------------------------------------------- 
    */

    // biến thể sản phẩm
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // sản phẩm (thông qua variant)
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariant::class,
            'id',
            'id',
            'variant_id',
            'product_id'
        );
    }

    // người tạo phiếu nhập
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (LOGIC CHÍNH)
    |-------------------------------------------------------------------------- 
    */

    // 🔥 SỐ ĐÃ BÁN (không bao giờ âm)
    public function getSoldQuantityAttribute()
    {
        return max(
            ($this->imported_quantity ?? 0)
                - ($this->remaining_quantity ?? 0)
                - ($this->expired_quantity ?? 0),
            0
        );
    }

    // 🔥 TRẠNG THÁI LÔ
    public function getStatusAttribute()
    {
        if ($this->expired_at) {
            return 'expired';
        }

        if (!$this->expiry_date) {
            return 'normal';
        }

        $months = now()->diffInMonths($this->expiry_date, false);

        if ($months <= 6) return 'danger';
        if ($months <= 7) return 'sale';

        return 'normal';
    }

    // 🔥 SỐ THÁNG CÒN LẠI
    public function getMonthsLeftAttribute()
    {
        if (!$this->expiry_date) return null;

        return now()->diffInMonths($this->expiry_date, false);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO FIX DATA (OPTIONAL - RẤT HAY)
    |-------------------------------------------------------------------------- 
    */

    protected static function booted()
    {
        static::creating(function ($model) {

            // 🔥 đảm bảo imported luôn đúng
            if (empty($model->imported_quantity)) {
                $model->imported_quantity = $model->quantity ?? 0;
            }

            // 🔥 đảm bảo remaining luôn có
            if (empty($model->remaining_quantity)) {
                $model->remaining_quantity = $model->quantity ?? 0;
            }

            // 🔥 expired mặc định = 0
            if (empty($model->expired_quantity)) {
                $model->expired_quantity = 0;
            }
        });
    }
}