<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'name_snapshot',
        'price',
        'cost_price',
        'qty',
        'line_total',
        'line_cost_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'qty' => 'integer',
            'line_total' => 'decimal:2',
            'line_cost_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    public function getResolvedLineCostTotalAttribute(): float
    {
        $lineCostTotal = (float) $this->line_cost_total;
        if ($lineCostTotal > 0) {
            return $lineCostTotal;
        }

        $unitCost = (float) $this->cost_price;
        if ($unitCost <= 0 && $this->product) {
            $unitCost = (float) $this->product->cost_price;
        }

        return round($unitCost * (int) $this->qty, 2);
    }
}
