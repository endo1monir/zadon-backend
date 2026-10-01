<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['owner_id', 'category_id', 'city_id', 'name_ar', 'name_en', 'logo', 'cover_image', 'rating', 'rating_count', 'address', 'phone', 'email', 'cr_number', 'vat_number', 'status', 'prep_time_min', 'delivery_fee', 'min_order', 'manager_name', 'is_verified', 'is_open_24_7', 'opening_time', 'closing_time', 'delivery_radius_km', 'is_active'])]
class Store extends Model
{
    use HasFactory;

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
            'delivery_fee' => 'decimal:2',
            'min_order' => 'decimal:2',
            'delivery_radius_km' => 'decimal:2',
            'prep_time_min' => 'integer',
            'is_verified' => 'boolean',
            'is_open_24_7' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
