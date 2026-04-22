<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public bool $forAdmin;


    public function __construct(Order $order, bool $forAdmin = false)
    {
        $this->order = $order->load([
            'items.variant.product.images',
            'user'
        ]);

        $this->forAdmin = $forAdmin;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? 'Có đơn hàng mới #' . $this->order->id . ' - ELARA'
                : 'Xác nhận đơn hàng #' . $this->order->id . ' - ELARA'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_created',
            with: [
                'order'    => $this->order,
                'user'     => $this->order->user,
                'forAdmin' => $this->forAdmin
            ]
        );
    }


    public function attachments(): array
    {
        return [];
    }
}