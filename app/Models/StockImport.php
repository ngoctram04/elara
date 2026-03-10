<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockImport extends Model
{
    protected $table = 'stock_imports';

    protected $fillable = [
        'variant_id',
        'quantity',
        'cost_price',
        'manufacture_date',
        'expiry_date',
        'code',
        'supplier',
        'note',
        'created_by'
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date'      => 'date',
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
            'id',          // FK trên product_variants
            'id',          // FK trên products
            'variant_id',  // local key stock_imports
            'product_id'   // local key product_variants
        );
    }

    // người tạo phiếu nhập
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}