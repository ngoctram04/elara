<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Tạo notification
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Kênh gửi
     */
    public function via($notifiable)
    {
        return ['database']; // sau này có thể thêm broadcast
    }

    /**
     * Lưu DB
     */
    public function toDatabase($notifiable)
    {
        return [
            'title'   => $this->data['title'] ?? 'Thông báo',
            'message' => $this->data['message'] ?? '',
            'url'     => $this->data['url'] ?? '/',
            'type'    => $this->data['type'] ?? 'system',

            // UI
            'icon'  => $this->data['icon'] ?? $this->getDefaultIcon(),
            'color' => $this->data['color'] ?? $this->getDefaultColor(),

            // dữ liệu thêm
            'meta' => $this->data['meta'] ?? [],
        ];
    }

    /**
     * Array (dùng cho broadcast sau này)
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Icon theo type
     */
    private function getDefaultIcon()
    {
        return match ($this->data['type'] ?? 'system') {

            // ===== ORDER =====
            'order_created'   => 'bi-bag-plus',
            'order_cancelled' => 'bi-x-circle',
            'order_completed' => 'bi-check-circle',
            'order_paid'      => 'bi-credit-card',

            // ===== REVIEW =====
            'review'         => 'bi-star',
            'review_reply'   => 'bi-reply',

            // ===== OTHER =====
            'chat'       => 'bi-chat-dots',
            'voucher'    => 'bi-ticket-perforated',
            'promotion'  => 'bi-fire',

            default      => 'bi-bell'
        };
    }

    /**
     * Màu theo type
     */
    private function getDefaultColor()
    {
        return match ($this->data['type'] ?? 'system') {

            // ===== ORDER =====
            'order_created'   => 'primary',
            'order_cancelled' => 'danger',
            'order_completed' => 'success',
            'order_paid'      => 'info',

            // ===== REVIEW =====
            'review'         => 'warning',
            'review_reply'   => 'info',

            // ===== OTHER =====
            'chat'       => 'success',
            'voucher'    => 'warning',
            'promotion'  => 'danger',

            default      => 'secondary'
        };
    }
}