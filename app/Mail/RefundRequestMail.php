<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class RefundRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $refund;

    public function __construct($order, $refund)
    {
        $this->order = $order;
        $this->refund = $refund;
    }

    public function build()
    {
        return $this->subject('Có yêu cầu hoàn tiền mới - Đơn #' . $this->order->id)
            ->view('emails.refund-request')
            ->with([
                'order' => $this->order,
                'refund' => $this->refund
            ]);
    }
}