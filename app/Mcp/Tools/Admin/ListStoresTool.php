<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Store;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListStoresTool extends AdminTool
{
    protected function key(): string
    {
        return 'list_stores';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description($this->parameter('search')),
            'status' => $schema->string()->enum(['active', 'inactive', 'all'])->description($this->parameter('status')),
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Store::query()
            ->with('owner:id,name,phone,is_active')
            ->withCount(['products', 'orders'])
            ->withSum(['orders as revenue' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'total')
            ->when(filled($request->get('search')), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->get('search')).'%';

                $query->where(fn ($query) => $query->where('name_ar', 'like', $term)->orWhere('name_en', 'like', $term));
            })
            ->when($request->get('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->get('status') === 'inactive', fn ($query) => $query->where('is_active', false));

        $total = (clone $query)->count();

        return $this->json([
            'found' => $total,
            'stores' => $query->orderByDesc('revenue')->limit($this->limit($request))->get()->map(fn (Store $store): array => [
                'id' => $store->id,
                'name' => $this->nameOf($store),
                'active' => (bool) $store->is_active,
                'open_status' => $store->status,
                'verified' => (bool) $store->is_verified,
                'rating' => $store->rating !== null ? (float) $store->rating : null,
                'ratings' => $store->rating_count,
                'phone' => $store->phone,
                'owner' => $store->owner ? [
                    'user_id' => $store->owner->id,
                    'name' => $store->owner->name,
                    'phone' => $store->owner->phone,
                    'active' => (bool) $store->owner->is_active,
                ] : null,
                'products' => $store->products_count,
                'orders' => $store->orders_count,
                'revenue_sar' => $this->money($store->revenue),
            ])->all(),
        ]);
    }
}
