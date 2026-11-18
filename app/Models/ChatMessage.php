<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One message in a chatbot conversation. Assistant messages also keep the
 * ids of the products and orders shown with them, so history can redraw them.
 */
#[Fillable(['chat_conversation_id', 'role', 'content', 'product_ids', 'order_ids'])]
class ChatMessage extends Model
{
    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
            'order_ids' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }
}
