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

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        // Load đầy đủ quan hệ để tránh lỗi khi queue
        $this->order = $order->load([
            'items.variant.product.images',
            'user'
        ]);
    }

    /**
     * Email subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn hàng #' . $this->order->id . ' đã giao thành công - ELARA'
        );
    }

    /**
     * Email content
     */
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

    /**
     * Attachments (có thể thêm PDF hóa đơn sau này)
     */
    public function attachments(): array
    {
        return [];
    }
}