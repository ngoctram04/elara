<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'variant_id',
        'stock_import_id',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'unit_cost',
        'loss_amount',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'variant_id'      => 'integer',
        'stock_import_id' => 'integer',
        'quantity_change' => 'integer',
        'stock_before'    => 'integer',
        'stock_after'     => 'integer',
        'unit_cost'       => 'float',
        'loss_amount'     => 'float',
        'reference_id'    => 'integer',
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

    public function stockImport()
    {
        return $this->belongsTo(StockImport::class, 'stock_import_id');
    }
}