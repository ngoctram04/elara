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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->withDefault();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
            ->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
            ->latestOfMany();
    }


    public function getLastMessagePreviewAttribute()
    {
        if (!$this->lastMessage) {
            return '';
        }

        return $this->lastMessage->preview ?? '';
    }

    public function getLastMessageTimeAttribute()
    {
        if (!$this->lastMessage) {
            return '';
        }

        return $this->lastMessage->created_at
            ? $this->lastMessage->created_at->format('H:i')
            : '';
    }


    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function close()
    {
        $this->update([
            'status' => 'closed'
        ]);
    }

    public function reopen()
    {
        $this->update([
            'status' => 'open'
        ]);
    }

    public function unreadMessages()
    {
        return $this->messages()
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead()
    {
        $this->messages()
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);
    }

    public function hasUnreadMessages()
    {
        return $this->unreadMessages() > 0;
    }
}