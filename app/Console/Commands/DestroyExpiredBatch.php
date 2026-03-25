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

            /*
            =====================================
            🔥 LẤY LÔ CẦN HUỶ
            =====================================
            */
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

            /*
            =====================================
            🔥 HUỶ TỪNG LÔ
            =====================================
            */
            foreach ($expiredLots as $lot) {

                $variant = ProductVariant::lockForUpdate()->find($lot->variant_id);
                if (!$variant) continue;

                $qty = (int) $lot->remaining_quantity;
                if ($qty <= 0) continue;

                $before = (int) $variant->stock_quantity;

                /*
                🔥 UPDATE BATCH
                */
                DB::table('stock_imports')
                    ->where('id', $lot->id)
                    ->update([
                        'expired_quantity'   => DB::raw("COALESCE(expired_quantity,0) + {$qty}"),
                        'remaining_quantity' => 0,
                        'expired_at'         => now(),
                        'updated_at'         => now(),
                    ]);

                /*
                🔥 TRỪ STOCK THẬT NGAY TẠI ĐÂY (QUAN TRỌNG)
                */
                $variant->decrement('stock_quantity', $qty);

                $after = $variant->fresh()->stock_quantity;

                /*
                🔥 LOG CHUẨN 100%
                */
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

            /*
            =====================================
            🔥 SYNC STOCK (BACKUP CHỐNG LỆCH)
            =====================================
            */
            DB::statement("
                UPDATE product_variants pv
                SET stock_quantity = (
                    SELECT COALESCE(SUM(si.remaining_quantity), 0)
                    FROM stock_imports si
                    WHERE si.variant_id = pv.id
                )
            ");

            DB::statement("
                UPDATE products p
                SET total_stock = (
                    SELECT COALESCE(SUM(pv.stock_quantity), 0)
                    FROM product_variants pv
                    WHERE pv.product_id = p.id
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