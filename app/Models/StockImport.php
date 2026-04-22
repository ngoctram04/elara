<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockImport extends Model
{
    protected $table = 'stock_imports';

    protected $fillable = [
        'variant_id',
        'quantity',
        'remaining_quantity',
        'imported_quantity',
        'expired_quantity',

        'cost_price',
        'manufacture_date',
        'expiry_date',

        'expired_at',

        'code',
        'supplier',
        'supplier_phone',
        'supplier_address',
        'note',
        'created_by'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date'      => 'date',
        'expired_at'       => 'datetime',

        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];



    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    public function getSoldQuantityAttribute()
    {
        return max(
            ($this->imported_quantity ?? 0)
                - ($this->remaining_quantity ?? 0)
                - ($this->expired_quantity ?? 0),
            0
        );
    }

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

    public function getMonthsLeftAttribute()
    {
        if (!$this->expiry_date) return null;

        return now()->diffInMonths($this->expiry_date, false);
    }



    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->imported_quantity)) {
                $model->imported_quantity = $model->quantity ?? 0;
            }

            if (empty($model->remaining_quantity)) {
                $model->remaining_quantity = $model->quantity ?? 0;
            }

            if (empty($model->expired_quantity)) {
                $model->expired_quantity = 0;
            }
        });

        static::saved(function ($model) {
            if ($model->variant) {
                $model->variant->syncStockAndStatus();
            }
        });

        static::deleted(function ($model) {
            if ($model->variant) {
                $model->variant->syncStockAndStatus();
            }
        });
    }
}