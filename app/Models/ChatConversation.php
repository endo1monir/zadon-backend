<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One chatbot conversation. Its uuid is the `conversation_id` the app sends
 * back to continue the chat or read its history. Guests get one too
 * (user_id is null); a logged-in customer's conversation is theirs only.
 */
#[Fillable(['user_id'])]
class ChatConversation extends Model
{
    use HasUuids;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    /**
     * Guest conversations are open to whoever holds the id; a customer's
     * conversation only to that customer.
     */
    public function isAccessibleBy(?User $user): bool
    {
        return $this->user_id === null || ($user !== null && (int) $this->user_id === $user->id);
    }
}
