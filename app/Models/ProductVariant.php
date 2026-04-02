<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'attribute_name',
        'attribute_value',
        'price',
        'cost_price',
        'stock_quantity',
        'sold_quantity',
        'is_active',
    ];

    protected $casts = [
        'price'          => 'float',
        'cost_price'     => 'float',
        'stock_quantity' => 'integer',
        'sold_quantity'  => 'integer',
        'is_active'      => 'boolean',
    ];

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

    public function promotions()
    {
        return $this->hasManyThrough(
            Promotion::class,
            PromotionProduct::class,
            'variant_id',
            'id',
            'id',
            'promotion_id'
        );
    }

    public function stockImports(): HasMany
    {
        return $this->hasMany(StockImport::class, 'variant_id');
    }

    public function getImagePathAttribute()
    {
        return optional($this->mainImage)->image_path;
    }

    public function availableStock(): int
    {
        return (int) $this->stockImports()
            ->where('remaining_quantity', '>', 0)
            ->sum('remaining_quantity');
    }

    public function isInStock(): bool
    {
        return $this->availableStock() > 0;
    }

    public function syncStockAndStatus(): void
    {
        $total = (int) $this->stockImports()->sum('remaining_quantity');

        $this->stock_quantity = $total;
        $this->is_active = $total > 0;
        $this->save();
    }

    public function displayName(): string
    {
        return "{$this->attribute_name}: {$this->attribute_value}";
    }

    public function activePromotion(): ?Promotion
    {
        if ($this->relationLoaded('promotions')) {
            return $this->promotions
                ->where('type', 'product')
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->sortByDesc('discount_value')
                ->first();
        }

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
            return max(0, round($this->price * (1 - $promotion->discount_value / 100), 0));
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

    public function getProfitPerItemAttribute(): float
    {
        return $this->final_price - $this->cost_price;
    }

    public function deductByBatch(int $quantity): array
    {
        return DB::transaction(function () use ($quantity) {
            $variant = self::where('id', $this->id)->lockForUpdate()->firstOrFail();

            if ($variant->stock_quantity < $quantity) {
                throw new \Exception('Không đủ hàng trong kho');
            }

            $before = $variant->stock_quantity;

            $batches = $variant->stockImports()
                ->where('remaining_quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;
            $usedBatches = [];
            $totalCost = 0;

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, $batch->remaining_quantity);

                $batch->remaining_quantity -= $take;
                $batch->save();

                $usedBatches[] = [
                    'batch_id'   => $batch->id,
                    'quantity'   => $take,
                    'cost_price' => (float) ($batch->cost_price ?? 0),
                ];

                $totalCost += $take * (float) ($batch->cost_price ?? 0);
                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw new \Exception('Không đủ tồn kho theo lô');
            }

            $variant->syncStockAndStatus();
            $variant->refresh();

            $after = $variant->stock_quantity;

            \App\Models\InventoryLog::create([
                'variant_id'      => $variant->id,
                'type'            => 'order',
                'quantity_change' => -$quantity,
                'stock_before'    => $before,
                'stock_after'     => $after,
            ]);

            return [
                'batches'    => $usedBatches,
                'total_cost' => $totalCost,
            ];
        });
    }

    public function restoreStock(int $quantity): void
    {
        DB::transaction(function () use ($quantity) {
            $variant = self::where('id', $this->id)->lockForUpdate()->firstOrFail();

            $before = $variant->stock_quantity;

            $latestBatch = $variant->stockImports()
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->first();

            if ($latestBatch) {
                $latestBatch->remaining_quantity += $quantity;
                $latestBatch->save();
            } else {
                throw new \Exception('Không có lô hàng để hoàn lại tồn kho');
            }

            $variant->syncStockAndStatus();
            $variant->refresh();

            $after = $variant->stock_quantity;

            \App\Models\InventoryLog::create([
                'variant_id'      => $variant->id,
                'type'            => 'cancel',
                'quantity_change' => $quantity,
                'stock_before'    => $before,
                'stock_after'     => $after,
            ]);
        });
    }
}