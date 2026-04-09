<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class AutoConfirmDeliveredOrders extends Command
{

    protected $signature = 'orders:auto-confirm-delivered';
    protected $description = 'Tự động xác nhận đơn hàng sau 7 ngày nếu khách chưa xác nhận';

    public function handle(): int
    {
        $this->info('Bắt đầu kiểm tra đơn hàng cần tự động xác nhận...');

        $orders = Order::where('status', Order::STATUS_COMPLETED)
            ->where(function ($query) {
                $query->where('customer_confirmed', false)
                    ->orWhereNull('customer_confirmed');
            })
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDays(7))
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $order->customer_confirmed = true;
            $order->received_at = $order->received_at ?? now();
            $order->save();

            $count++;
        }

        $this->info("Đã tự động xác nhận {$count} đơn hàng.");

        return self::SUCCESS;
    }
}