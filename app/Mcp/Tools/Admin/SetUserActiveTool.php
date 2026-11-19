<?php

namespace App\Mcp\Tools\Admin;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

/**
 * Customers and vendors only. Admin accounts stay in the admin panel, so an
 * assistant can never lock admins out.
 */
#[IsIdempotent]
class SetUserActiveTool extends AdminAction
{
    protected function key(): string
    {
        return 'set_user_active';
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->integer()->description($this->parameter('user_id'))->required(),
            'active' => $schema->boolean()->description($this->parameter('active'))->required(),
        ];
    }

    protected function prepare(Request $request): string|array
    {
        $user = User::query()->whereIn('role', ['customer', 'vendor'])->find((int) $request->get('user_id'));
        $active = $this->boolean($request->get('active'));

        if (! $user) {
            return 'No customer or vendor with this id (admin accounts cannot be changed here). Use search_users to find it.';
        }

        if ($active === null) {
            return 'Send active as true or false.';
        }

        if ((bool) $user->is_active === $active) {
            return "Nothing to change: the {$user->role} is already ".($active ? 'active' : 'blocked').'.';
        }

        $summary = ($active ? 'Activate' : 'Block')." the {$user->role} account \"{$user->name}\" ({$user->phone}, #{$user->id}).";

        return [$summary, function () use ($user, $active): array {
            $user->update(['is_active' => $active]);

            return ['user_id' => $user->id, 'active' => $active];
        }];
    }
}
