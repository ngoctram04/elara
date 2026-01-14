<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    // Danh mục cha
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Danh mục con
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderByDesc('created_at');
    }
    // Sản phẩm thuộc danh mục con
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}