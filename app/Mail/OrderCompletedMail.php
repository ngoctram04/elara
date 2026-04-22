<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;


    public function __construct(Order $order)
    {
        $this->order = $order->load([
            'items.variant.product.images',
            'user'
        ]);
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn hàng #' . $this->order->id . ' đã giao thành công - ELARA'
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.order_completed',
            with: [
                'order' => $this->order,
                'user'  => $this->order->user
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}