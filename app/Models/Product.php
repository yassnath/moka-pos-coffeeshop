<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'price',
        'cost_price',
        'is_active',
        'track_stock',
        'stock_qty',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'stock_qty' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $relativePath = ltrim((string) $this->image_path, '/');

        // Primary source: storage/public with /storage symlink.
        if (is_file(public_path('storage/'.$relativePath))) {
            return asset('storage/'.$relativePath);
        }

        // Fallback: image exists directly in /public (used by seeded catalog assets).
        if (is_file(public_path($relativePath))) {
            return asset($relativePath);
        }

        $basename = basename($relativePath);

        if (is_file(public_path($basename))) {
            return asset($basename);
        }

        return null;
    }
}
