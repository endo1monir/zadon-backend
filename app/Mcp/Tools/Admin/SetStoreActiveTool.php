<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Store;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class SetStoreActiveTool extends AdminAction
{
    protected function key(): string
    {
        return 'set_store_active';
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'store_id' => $schema->integer()->description($this->parameter('store_id'))->required(),
            'active' => $schema->boolean()->description($this->parameter('active'))->required(),
        ];
    }

    protected function prepare(Request $request): string|array
    {
        $store = Store::query()->withCount('products')->find((int) $request->get('store_id'));
        $active = $this->boolean($request->get('active'));

        if (! $store) {
            return 'No store with this id. Use list_stores to find it.';
        }

        if ($active === null) {
            return 'Send active as true or false.';
        }

        if ((bool) $store->is_active === $active) {
            return 'Nothing to change: the store is already '.($active ? 'active' : 'inactive').'.';
        }

        $summary = ($active ? 'Activate' : 'Deactivate')." the store \"{$this->nameOf($store)}\" (#{$store->id}, {$store->products_count} products).";

        return [$summary, function () use ($store, $active): array {
            $store->update(['is_active' => $active]);

            return ['store_id' => $store->id, 'active' => $active];
        }];
    }
}
