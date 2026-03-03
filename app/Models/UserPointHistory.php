<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPointHistory extends Model
{
    use HasFactory;

    protected $table = 'user_point_histories';

    protected $fillable = [
        'user_id',
        'points',
        'type',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}