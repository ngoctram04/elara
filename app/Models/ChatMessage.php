<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'images',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'images'  => 'array'
    ];

    public $timestamps = true;


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Thuộc cuộc trò chuyện
    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    // Người gửi tin nhắn
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault();
    }


    /*
    |--------------------------------------------------------------------------
    | Helper functions
    |--------------------------------------------------------------------------
    */

    // Tin nhắn của user
    public function isFromUser()
    {
        return $this->sender && $this->sender->role === 'customer';
    }

    // Tin nhắn của admin
    public function isFromAdmin()
    {
        return $this->sender && $this->sender->role === 'admin';
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Lấy danh sách ảnh
    public function getImagesListAttribute()
    {
        return $this->images ?? [];
    }

    // Kiểm tra có ảnh
    public function hasImages()
    {
        return !empty($this->images);
    }

    // Preview tin nhắn (dùng trong danh sách chat)
    public function getPreviewAttribute()
    {
        if (!empty($this->message)) {
            return Str::limit($this->message, 40);
        }

        if ($this->hasImages()) {
            return '[Hình ảnh]';
        }

        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers khác
    |--------------------------------------------------------------------------
    */

    // Kiểm tra có nội dung
    public function hasContent()
    {
        return !empty($this->message) || $this->hasImages();
    }

    // Lấy thời gian format
    public function getTimeAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('H:i')
            : '';
    }

    // Lấy ngày format
    public function getDateAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y')
            : '';
    }
}