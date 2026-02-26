<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewMedia extends Model
{
    use HasFactory;

    protected $table = 'review_media';

    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'file_path',
        'file_type',
    ];

    protected $casts = [
        'file_type' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function isImage()
    {
        return $this->file_type === 'image';
    }

    public function isVideo()
    {
        return $this->file_type === 'video';
    }
}