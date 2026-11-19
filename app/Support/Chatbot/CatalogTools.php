<?php

namespace App\Support\Chatbot;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Prism\Prism\Tool;

/**
 * Tools anyone can use, logged in or not: offers, product search and categories.
 */
class CatalogTools
{
    /**
     * @return list<Tool>
     */
    public function tools(ChatSession $session): array
    {
        return [
            self::define('list_offers')
                ->withStringParameter('category', config('ai.tools.list_offers.parameters.category'), required: false)
                ->using(fn (?string $category = null): string => $this->listOffers($session, $category)),

            self::define('search_products')
                ->withStringParameter('query', config('ai.tools.search_products.parameters.query'))
                ->withNumberParameter('max_price', config('ai.tools.search_products.parameters.max_price'), required: false)
                ->using(fn (string $query, int|float|null $max_price = null): string => $this->searchProducts($session, $query, $max_price)),

            self::define('list_categories')
                ->withStringParameter('parent_only', config('ai.tools.list_categories.parameters.parent_only'), required: false)
                ->using(fn (?string $parent_only = null): string => $this->listCategories($parent_only !== 'no')),
        ];
    }

    /**
     * A tool named and described from config/ai.php.
     */
    public static function define(string $name): Tool
    {
        return (new Tool)->as($name)->for(config("ai.tools.{$name}.description"));
    }

    /**
     * Compact JSON of products for the model; also remembers them as cards.
     *
     * @param  Collection<int, Product>  $products
     */
    public static function describeProducts(ChatSession $session, Collection $products): string
    {
        $session->rememberProducts($products->modelKeys());

        if ($products->isEmpty()) {
            return json_encode(['products' => [], 'note' => 'No matching products found.']);
        }

        return json_encode([
            'products' => $products->map(fn (Product $product): array => array_filter([
                'id' => $product->id,
                'name' => $session->localized($product->name_ar, $product->name_en),
                'price_sar' => (float) $product->price,
                'original_price_sar' => $product->original_price !== null ? (float) $product->original_price : null,
                'discount_percent' => self::discountPercent($product),
                'in_stock' => $product->stock,
                'unit' => $session->localized($product->unit_ar, $product->unit_en),
                'store' => $session->localized($product->store?->name_ar, $product->store?->name_en),
                'category' => $session->localized($product->category?->name_ar, $product->category?->name_en),
            ], fn (mixed $value): bool => $value !== null))->all(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Discounted products, biggest discount first. Shared with the MCP server.
     */
    public function listOffers(ChatSession $session, ?string $category): string
    {
        $products = $this->availableProducts()
            ->whereNotNull('original_price')
            ->whereColumn('original_price', '>', 'price')
            ->when(filled($category), fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $categoryQuery) => $categoryQuery
                    ->where('name_ar', 'like', "%{$category}%")
                    ->orWhere('name_en', 'like', "%{$category}%")
            ))
            ->orderByRaw('(original_price - price) * 1.0 / original_price desc')
            ->limit(config('ai.chat.max_products'))
            ->get();

        return self::describeProducts($session, $products);
    }

    /**
     * Products matching the words, best sellers first. Shared with the MCP server.
     */
    public function searchProducts(ChatSession $session, string $query, int|float|null $maxPrice): string
    {
        $products = $this->availableProducts()
            ->search($query)
            ->when($maxPrice !== null && $maxPrice > 0, fn (Builder $builder) => $builder->where('price', '<=', $maxPrice))
            ->orderByDesc('sales_count')
            ->limit(config('ai.chat.max_products'))
            ->get();

        return self::describeProducts($session, $products);
    }

    /**
     * Active product categories. Shared with the MCP server.
     */
    public function listCategories(bool $parentOnly): string
    {
        $categories = Category::query()
            ->active()
            ->type('product')
            ->when($parentOnly, fn (Builder $query) => $query->whereNull('parent_id'))
            ->orderBy('sort_order')
            ->get(['name_ar', 'name_en']);

        return json_encode([
            'categories' => $categories->map(fn (Category $category): array => [
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
            ])->all(),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return Builder<Product>
     */
    private function availableProducts(): Builder
    {
        return Product::query()->purchasable()->with('store', 'category');
    }

    private static function discountPercent(Product $product): ?int
    {
        if ($product->original_price === null || (float) $product->original_price <= (float) $product->price) {
            return null;
        }

        return (int) round((1 - (float) $product->price / (float) $product->original_price) * 100);
    }
}
