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



    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault();
    }


    public function isFromUser()
    {
        return $this->sender && $this->sender->role === 'customer';
    }

    public function isFromAdmin()
    {
        return $this->sender && $this->sender->role === 'admin';
    }


    public function getImagesListAttribute()
    {
        return $this->images ?? [];
    }

   
    public function hasImages()
    {
        return !empty($this->images);
    }

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


    public function hasContent()
    {
        return !empty($this->message) || $this->hasImages();
    }


    public function getTimeAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('H:i')
            : '';
    }

    public function getDateAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y')
            : '';
    }
}