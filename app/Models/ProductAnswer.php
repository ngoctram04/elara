<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAnswer extends Model
{
    use HasFactory;

    protected $table = 'product_answers';

    protected $fillable = [
        'question_id',
        'user_id',
        'answer',
        'is_admin'
    ];

    /**
     * Quan hệ với câu hỏi
     */
    public function question()
    {
        return $this->belongsTo(ProductQuestion::class, 'question_id');
    }

    /**
     * Người trả lời
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}