<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemBatch extends Model
{
    protected $table = 'order_item_batches';

    protected $fillable = [
        'order_item_id',
        'stock_import_id',
        'quantity',
        'returned_quantity',
        'is_rolled_back',
    ];
    protected $casts = [
        'quantity'          => 'integer',
        'returned_quantity' => 'integer',
        'is_rolled_back'    => 'boolean',
    ];



    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function stockImport(): BelongsTo
    {
        return $this->belongsTo(StockImport::class, 'stock_import_id');
    }
}