<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'user_id',
        'admin_id',
        'status'
    ];

    protected $attributes = [
        'status' => 'open'
    ];

    public $timestamps = true;


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Khách hàng
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    // Admin hỗ trợ
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->withDefault();
    }

    // Danh sách tin nhắn
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
            ->orderBy('created_at', 'asc');
    }

    // Tin nhắn mới nhất
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
            ->latestOfMany();
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Lấy preview tin nhắn cuối
    public function getLastMessagePreviewAttribute()
    {
        if (!$this->lastMessage) {
            return '';
        }

        return $this->lastMessage->preview ?? '';
    }

    // Thời gian tin nhắn cuối
    public function getLastMessageTimeAttribute()
    {
        if (!$this->lastMessage) {
            return '';
        }

        return $this->lastMessage->created_at
            ? $this->lastMessage->created_at->format('H:i')
            : '';
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    // Kiểm tra conversation còn mở
    public function isOpen()
    {
        return $this->status === 'open';
    }

    // Đóng conversation
    public function close()
    {
        $this->update([
            'status' => 'closed'
        ]);
    }

    // Mở conversation
    public function reopen()
    {
        $this->update([
            'status' => 'open'
        ]);
    }

    // Đếm tin nhắn chưa đọc
    public function unreadMessages()
    {
        return $this->messages()
            ->where('is_read', false)
            ->count();
    }

    // Đánh dấu đã đọc
    public function markAsRead()
    {
        $this->messages()
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);
    }

    // Kiểm tra có tin nhắn chưa đọc
    public function hasUnreadMessages()
    {
        return $this->unreadMessages() > 0;
    }
}