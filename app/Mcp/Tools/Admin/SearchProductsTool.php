<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SearchProductsTool extends AdminTool
{
    protected function key(): string
    {
        return 'search_products';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description($this->parameter('search')),
            'store_id' => $schema->integer()->description($this->parameter('store_id')),
            'status' => $schema->string()->enum(['active', 'inactive', 'all'])->description($this->parameter('status')),
            'low_stock' => $schema->boolean()->description($this->parameter('low_stock')),
            'sort' => $schema->string()->enum(['best_selling', 'stock', 'price', 'newest'])->description($this->parameter('sort')),
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Product::query()
            ->with('store:id,name_ar,name_en', 'category:id,name_ar,name_en')
            ->when(filled($request->get('search')), fn ($query) => $query->search(trim((string) $request->get('search'))))
            ->when(filled($request->get('store_id')), fn ($query) => $query->where('store_id', (int) $request->get('store_id')))
            ->when($request->get('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->get('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($this->boolean($request->get('low_stock')), fn ($query) => $query->where(fn ($query) => $query
                ->where('stock', '<=', 0)
                ->orWhereColumn('stock', '<=', 'min_stock_alert')));

        match ($request->get('sort')) {
            'stock' => $query->orderBy('stock'),
            'price' => $query->orderByDesc('price'),
            'newest' => $query->latest('id'),
            default => $query->orderByDesc('sales_count'),
        };

        $total = (clone $query)->count();

        return $this->json([
            'found' => $total,
            'products' => $query->limit($this->limit($request))->get()->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $this->nameOf($product),
                'sku' => $product->sku,
                'store' => $this->nameOf($product->store),
                'category' => $this->nameOf($product->category),
                'price_sar' => $this->money($product->price),
                'original_price_sar' => $product->original_price !== null ? $this->money($product->original_price) : null,
                'cost_price_sar' => $product->cost_price !== null ? $this->money($product->cost_price) : null,
                'stock' => $product->stock,
                'low_stock_alert_at' => $product->min_stock_alert,
                'sold' => $product->sales_count,
                'active' => (bool) $product->is_active,
                'expiry_date' => $product->expiry_date?->toDateString(),
            ])->all(),
        ]);
    }
}
