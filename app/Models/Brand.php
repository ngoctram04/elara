<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
    ];

    // 1 thương hiệu có nhiều sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}