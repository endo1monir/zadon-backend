<?php

namespace App\Mcp\Tools\Admin;

use App\Models\ContactMessage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListContactMessagesTool extends AdminTool
{
    protected function key(): string
    {
        return 'list_contact_messages';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        return $this->json([
            'found' => ContactMessage::count(),
            'messages' => ContactMessage::query()
                ->with('user:id,name,phone,email')
                ->latest('id')
                ->limit($this->limit($request))
                ->get()
                ->map(fn (ContactMessage $message): array => [
                    'id' => $message->id,
                    'title' => $message->title,
                    'message' => $message->message,
                    'from' => $message->user ? [
                        'user_id' => $message->user->id,
                        'name' => $message->user->name,
                        'phone' => $message->user->phone,
                        'email' => $message->user->email,
                    ] : null,
                    'sent_at' => $message->created_at?->toDateTimeString(),
                ])
                ->all(),
        ]);
    }
}
