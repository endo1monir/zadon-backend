<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['store_id', 'category_id', 'name_ar', 'name_en', 'sku', 'barcode', 'price', 'original_price', 'cost_price', 'stock', 'min_stock_alert', 'unit_ar', 'unit_en', 'image', 'country_of_origin', 'storage_method', 'is_prescription_required', 'storage_temp', 'expiry_date', 'is_active', 'sales_count', 'description_ar', 'description_en', 'options'])]
class Product extends Model
{
    #[Scope]
    protected function purchasable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('stock', '>', 0);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): Builder
    {
        return $query->when(
            $term,
            fn (Builder $q) => $q->where(fn (Builder $inner) => $inner
                ->where('name_ar', 'like', "%{$term}%")
                ->orWhere('name_en', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%"))
        );
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock_alert' => 'integer',
            'sales_count' => 'integer',
            'is_prescription_required' => 'boolean',
            'is_active' => 'boolean',
            'expiry_date' => 'date',
            'options' => 'array',
        ];
    }
}