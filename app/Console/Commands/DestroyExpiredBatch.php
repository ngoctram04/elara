<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\InventoryLog;

class DestroyExpiredBatch extends Command
{
    protected $signature = 'inventory:destroy-expired';
    protected $description = 'Huỷ lô cận date (<= 6 tháng)';

    public function handle()
    {
        DB::beginTransaction();

        try {
            $expiredLots = DB::table('stock_imports')
                ->whereDate('expiry_date', '<=', now()->addMonths(6))
                ->whereNull('expired_at')
                ->where('remaining_quantity', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($expiredLots->isEmpty()) {
                $this->info('Không có lô nào cần huỷ');
                DB::rollBack();
                return;
            }

            foreach ($expiredLots as $lot) {
                $variant = ProductVariant::lockForUpdate()->find($lot->variant_id);
                if (!$variant) {
                    continue;
                }

                $qty = (int) $lot->remaining_quantity;
                if ($qty <= 0) {
                    continue;
                }

                $before = (int) $variant->stock_quantity;

                DB::table('stock_imports')
                    ->where('id', $lot->id)
                    ->update([
                        'expired_quantity'   => DB::raw("COALESCE(expired_quantity,0) + {$qty}"),
                        'remaining_quantity' => 0,
                        'expired_at'         => now(),
                        'updated_at'         => now(),
                    ]);

                $variant->decrement('stock_quantity', $qty);

                $after = (int) $variant->fresh()->stock_quantity;

                InventoryLog::create([
                    'variant_id'      => $variant->id,
                    'type'            => 'expired_destroy',
                    'quantity_change' => -$qty,
                    'stock_before'    => $before,
                    'stock_after'     => $after,
                    'reference_type'  => 'batch',
                    'reference_id'    => $lot->id,
                ]);
            }

            DB::statement("
                UPDATE product_variants pv
                SET stock_quantity = (
                    SELECT COALESCE(SUM(si.remaining_quantity), 0)
                    FROM stock_imports si
                    WHERE si.variant_id = pv.id
                )
            ");

            DB::commit();

            $this->info('Đã huỷ lô cận date thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Lỗi: ' . $e->getMessage());
        }
    }
}