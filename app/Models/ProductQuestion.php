<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    use HasFactory;

    protected $table = 'product_questions';

    protected $fillable = [
        'product_id',
        'user_id',
        'question',
        'is_active'
    ];

 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

   
    public function answers()
    {
        return $this->hasMany(ProductAnswer::class, 'question_id')
            ->orderBy('created_at', 'asc');
    }
}