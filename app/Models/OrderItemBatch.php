<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemBatch extends Model
{
    protected $table = 'order_item_batches';

    protected $fillable = [
        'order_item_id',
        'stock_import_id',
        'quantity',
    ];

    /* ========================
        RELATIONS
    ======================== */

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function stockImport()
    {
        return $this->belongsTo(StockImport::class);
    }
}