<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['order_number', 'user_id', 'store_id', 'status', 'payment_method', 'payment_status', 'subtotal', 'vat_amount', 'delivery_fee', 'total', 'delivery_address_id', 'delivery_address', 'city', 'customer_name', 'customer_phone', 'notes', 'courier_name', 'courier_phone', 'courier_eta_minutes', 'accepted_at', 'prepared_at', 'ready_at', 'out_for_delivery_at', 'delivered_at', 'cancelled_at'])]
class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'];

    /**
     * The single next step allowed from each status.
     */
    public const STATUS_FLOW = [
        'new' => ['preparing'],
        'preparing' => ['ready_for_pickup'],
        'ready_for_pickup' => ['out_for_delivery'],
        'out_for_delivery' => ['delivered'],
    ];

    /**
     * Statuses an order can no longer leave.
     */
    public const TERMINAL_STATUSES = ['delivered', 'cancelled'];

    public const STATUS_TIMESTAMPS = [
        'preparing' => 'accepted_at',
        'ready_for_pickup' => 'prepared_at',
        'out_for_delivery' => 'ready_at',
        'delivered' => 'delivered_at',
        'cancelled' => 'cancelled_at',
    ];

    /**
     * The statuses this order may move to next. A terminal order has none.
     *
     * @return list<string>
     */
    public function allowedStatusTransitions(): array
    {
        if (in_array($this->status, self::TERMINAL_STATUSES, true)) {
            return [];
        }

        return array_values(array_unique([
            ...self::STATUS_FLOW[$this->status] ?? [],
            'cancelled',
        ]));
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedStatusTransitions(), true);
    }

    /**
     * Tell the customer the order moved on, or was cancelled.
     */
    public function notifyCustomerStatusChange(bool $cancelled = false): void
    {
        if (! $this->user_id) {
            return;
        }

        $message = $cancelled
            ? ['title_ar' => 'تم إلغاء طلبك', 'title_en' => 'Your order was cancelled']
            : [
                'title_ar' => "حدث جديد على طلبك {$this->order_number}",
                'title_en' => "Your order {$this->order_number} was updated",
            ];

        $this->user->notifications()->create($message + [
            'type' => 'order',
            'order_id' => $this->id,
            'action_label_ar' => 'متابعة الطلب',
            'action_label_en' => 'Track order',
        ]);
    }

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
