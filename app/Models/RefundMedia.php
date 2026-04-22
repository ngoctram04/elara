<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundMedia extends Model
{
    use HasFactory;

    protected $table = 'refund_media';

    protected $fillable = [
        'refund_request_id',
        'file_path',
        'type'
    ];



    public function refundRequest()
    {
        return $this->belongsTo(RefundRequest::class, 'refund_request_id');
    }

 

    public function isImage()
    {
        return $this->type === 'image';
    }

    public function isVideo()
    {
        return $this->type === 'video';
    }

    public function url()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}