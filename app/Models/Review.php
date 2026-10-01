<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['store_id', 'user_id', 'order_id', 'customer_name', 'customer_avatar', 'rating', 'courier_rating', 'comment', 'tags', 'store_reply', 'store_reply_date', 'published'])]
class Review extends Model
{
    use HasFactory;

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'courier_rating' => 'integer',
            'tags' => 'array',
            'store_reply_date' => 'datetime',
            'published' => 'boolean',
        ];
    }
}
