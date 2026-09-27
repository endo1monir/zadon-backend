<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['order_number', 'user_id', 'store_id', 'status', 'payment_method', 'payment_status', 'subtotal', 'vat_amount', 'delivery_fee', 'total', 'delivery_address_id', 'delivery_address', 'city', 'customer_name', 'customer_phone', 'notes', 'courier_name', 'courier_phone', 'courier_eta_minutes', 'accepted_at', 'prepared_at', 'ready_at', 'out_for_delivery_at', 'delivered_at', 'cancelled_at'])]
class Order extends Model
{
    public const STATUSES = ['new', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'];

    public const STATUS_TIMESTAMPS = [
        'preparing' => 'accepted_at',
        'ready_for_pickup' => 'prepared_at',
        'out_for_delivery' => 'ready_at',
        'delivered' => 'delivered_at',
        'cancelled' => 'cancelled_at',
    ];

    public function setStatus(string $status): void
    {
        $this->status = $status;

        if ($column = self::STATUS_TIMESTAMPS[$status] ?? null) {
            $this->{$column} = now();
        }

        $this->save();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'key');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
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
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'courier_eta_minutes' => 'integer',
            'accepted_at' => 'datetime',
            'prepared_at' => 'datetime',
            'ready_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
