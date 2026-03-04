<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'status',
        'admin_note'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Người gửi yêu cầu
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Ảnh / video minh chứng
    public function media()
    {
        return $this->hasMany(RefundMedia::class, 'refund_request_id');
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isRefunded()
    {
        return $this->status === 'refunded';
    }
}