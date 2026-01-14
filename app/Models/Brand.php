<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    // 1 thương hiệu có nhiều sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}