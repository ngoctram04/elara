<?php

namespace App\Mail;

use App\Models\RefundRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundRejectedMail extends Mailable
{
    use SerializesModels;

    public $refund;

    public function __construct(RefundRequest $refund)
    {
        $this->refund = $refund;
    }

    public function build()
    {
        return $this->subject('Yêu cầu hoàn tiền bị từ chối cho đơn hàng #' . $this->refund->order_id)
            ->view('emails.refund_rejected');
    }
}