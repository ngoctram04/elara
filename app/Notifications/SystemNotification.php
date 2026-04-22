<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected $data;


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }


    public function toDatabase($notifiable)
    {
        return [
            'title'   => $this->data['title'] ?? 'Thông báo',
            'message' => $this->data['message'] ?? '',
            'url'     => $this->data['url'] ?? '/',
            'type'    => $this->data['type'] ?? 'system',

            
            'icon'  => $this->data['icon'] ?? $this->getDefaultIcon(),
            'color' => $this->data['color'] ?? $this->getDefaultColor(),


            'meta' => $this->data['meta'] ?? [],
        ];
    }

  
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }

   
    private function getDefaultIcon()
    {
        return match ($this->data['type'] ?? 'system') {

         
            'order_created'   => 'bi-bag-plus',
            'order_cancelled' => 'bi-x-circle',
            'order_completed' => 'bi-check-circle',
            'order_paid'      => 'bi-credit-card',

            
            'review'         => 'bi-star',
            'review_reply'   => 'bi-reply',

            'chat'       => 'bi-chat-dots',
            'voucher'    => 'bi-ticket-perforated',
            'promotion'  => 'bi-fire',

            default      => 'bi-bell'
        };
    }


    private function getDefaultColor()
    {
        return match ($this->data['type'] ?? 'system') {

     
            'order_created'   => 'primary',
            'order_cancelled' => 'danger',
            'order_completed' => 'success',
            'order_paid'      => 'info',

            'review'         => 'warning',
            'review_reply'   => 'info',
            'chat'       => 'success',
            'voucher'    => 'warning',
            'promotion'  => 'danger',

            default      => 'secondary'
        };
    }
}