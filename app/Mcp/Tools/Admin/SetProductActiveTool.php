<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class SetProductActiveTool extends AdminAction
{
    protected function key(): string
    {
        return 'set_product_active';
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->integer()->description($this->parameter('product_id'))->required(),
            'active' => $schema->boolean()->description($this->parameter('active'))->required(),
        ];
    }

    protected function prepare(Request $request): string|array
    {
        $product = Product::query()->with('store:id,name_ar,name_en')->find((int) $request->get('product_id'));
        $active = $this->boolean($request->get('active'));

        if (! $product) {
            return 'No product with this id. Use search_products to find it.';
        }

        if ($active === null) {
            return 'Send active as true or false.';
        }

        if ((bool) $product->is_active === $active) {
            return 'Nothing to change: the product is already '.($active ? 'active' : 'inactive').'.';
        }

        $summary = ($active ? 'Activate' : 'Deactivate (hide)')." the product \"{$this->nameOf($product)}\" (#{$product->id}) of {$this->nameOf($product->store)}.";

        return [$summary, function () use ($product, $active): array {
            $product->update(['is_active' => $active]);

            return ['product_id' => $product->id, 'active' => $active];
        }];
    }
}
