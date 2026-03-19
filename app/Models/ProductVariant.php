<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItemBatch;
use App\Models\StockImport;
class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'attribute_name',
        'attribute_value',
        'price',
        'original_price',
        'cost_price',
        'stock_quantity',
        'sold_quantity',
        'is_active',
    ];

    protected $casts = [
        'price'          => 'float',
        'original_price' => 'float',
        'cost_price'     => 'float',
        'stock_quantity' => 'integer',
        'sold_quantity'  => 'integer',
        'is_active'      => 'boolean',
    ];

    /* =====================================================
        RELATIONS
    ===================================================== */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(VariantImage::class, 'variant_id')
            ->where('is_main', 1);
    }

    public function promotionProducts(): HasMany
    {
        return $this->hasMany(PromotionProduct::class, 'variant_id');
    }

    // ⭐ Quan hệ trực tiếp promotion (dùng eager load)
    public function promotions()
    {
        return $this->hasManyThrough(
            Promotion::class,
            PromotionProduct::class,
            'variant_id',      // FK PromotionProduct
            'id',              // FK Promotion
            'id',              // local key variant
            'promotion_id'     // local key PromotionProduct
        );
    }

    public function stockImports(): HasMany
    {
        return $this->hasMany(StockImport::class, 'variant_id');
    }

    /* =====================================================
        IMAGE
    ===================================================== */

    public function getImagePathAttribute()
    {
        return optional($this->mainImage)->image_path;
    }

    /* =====================================================
        STOCK
    ===================================================== */

    public function availableStock(): int
    {
        return $this->batches()
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');
    }

    public function isInStock(): bool
    {
        return $this->availableStock() > 0;
    }

    /* =====================================================
        DISPLAY
    ===================================================== */

    public function displayName(): string
    {
        return "{$this->attribute_name}: {$this->attribute_value}";
    }

    /* =====================================================
        PROMOTION (OPTIMIZED)
    ===================================================== */

    public function activePromotion(): ?Promotion
    {
        // Nếu đã eager load promotions → không query nữa
        if ($this->relationLoaded('promotions')) {
            return $this->promotions
                ->where('type', 'product')
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->sortByDesc('discount_value')
                ->first();
        }

        // Nếu chưa load → query
        return $this->promotions()
            ->where('type', 'product')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('discount_value')
            ->first();
    }

    public function getFinalPriceAttribute(): float
    {
        $promotion = $this->activePromotion();

        if (!$promotion) {
            return (float) $this->price;
        }

        if ($promotion->discount_type === 'percent') {
            return max(0, round(
                $this->price * (1 - $promotion->discount_value / 100),
                0
            ));
        }

        return max(0, $this->price - $promotion->discount_value);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->activePromotion() !== null;
    }

    public function getDiscountAmountAttribute(): float
    {
        return max(0, $this->price - $this->final_price);
    }

    public function getDiscountLabelAttribute(): ?string
    {
        $promotion = $this->activePromotion();

        if (!$promotion) {
            return null;
        }

        return $promotion->discount_type === 'percent'
            ? "-{$promotion->discount_value}%"
            : '-' . number_format($promotion->discount_value, 0, ',', '.') . 'đ';
    }

    /* =====================================================
        PROFIT
    ===================================================== */

    public function getProfitPerItemAttribute(): float
    {
        return $this->final_price - $this->cost_price;
    }
    /* =====================================================
    BATCH (FEFO - HẾT HẠN TRƯỚC BÁN TRƯỚC)
===================================================== */
    public function syncStockAndStatus()
    {
        $total = $this->stockImports()->sum('remaining_quantity');

        $this->stock_quantity = $total;
        $this->is_active = $total > 0 ? 1 : 0;

        $this->save();
    }
    public function deductByBatch(int $quantity): array
    {
        return DB::transaction(function () use ($quantity) {

            $batches = StockImport::where('variant_id', $this->id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;
            $usedBatches = [];
            $totalCost = 0;

            foreach ($batches as $batch) {

                if ($remaining <= 0) break;

                $take = min($remaining, $batch->remaining_quantity);

                // Trừ tồn trong batch
                $batch->remaining_quantity -= $take;
                $batch->save();

                // Log theo batch (KHÔNG dùng stock_quantity trong loop)
                \App\Models\InventoryLog::create([
                    'variant_id'      => $this->id,
                    'type'            => 'order',
                    'quantity_change' => -$take,
                    'stock_before'    => 0, // optional
                    'stock_after'     => 0,
                    'reference_type'  => 'batch',
                    'reference_id'    => $batch->id,
                ]);

                $usedBatches[] = [
                    'batch_id'   => $batch->id,
                    'quantity'   => $take,
                    'cost_price' => $batch->cost_price,
                ];

                $totalCost += $take * $batch->cost_price;
                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw new \Exception('Không đủ tồn kho theo lô');
            }

            // ✅ QUAN TRỌNG: sync lại tổng tồn + trạng thái
            $this->syncStockAndStatus();

            return [
                'batches'    => $usedBatches,
                'total_cost' => $totalCost
            ];
        });
    }
    public function batches()
    {
        return $this->hasMany(StockImport::class, 'variant_id');
    }
}