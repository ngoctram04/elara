<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundCompletedMail extends Mailable
{
    use SerializesModels;

    public $order;
    public $amount;

    public function __construct($order, $amount)
    {
        $this->order = $order;
        $this->amount = $amount;
    }

    public function build()
    {
        return $this->subject('Đã hoàn tiền cho đơn hàng #' . $this->order->id)
            ->view('emails.refund_completed');
    }
}