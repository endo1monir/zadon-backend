<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inCartCount = $this->cartQuantities($request)[$this->id] ?? 0;

        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en,
            'price' => (float) $this->price,
            'original_price' => $this->original_price !== null ? (float) $this->original_price : null,
            'stock' => $this->stock,
            'unit' => app()->getLocale() === 'ar' ? $this->unit_ar : $this->unit_en,
            'image' => api_image($this->image),
            'country_of_origin' => $this->country_of_origin,
            'sales_count' => $this->sales_count,
            'description' => app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en,
            'in_cart' => $inCartCount > 0,
            'in_cart_count' => $inCartCount,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'store' => StoreResource::make($this->whenLoaded('store')),
        ];
    }

    /**
     * Quantities of the authenticated user's active cart keyed by product id.
     *
     * Resolved once per request so listing pages do not query per product.
     *
     * @return array<int, int>
     */
    public static function cartQuantities(Request $request): array
    {
        $attribute = 'product_cart_quantities';

        if ($request->attributes->has($attribute)) {
            return $request->attributes->get($attribute);
        }

        $user = $request->user('sanctum');

        $quantities = $user === null
            ? []
            : CartItem::query()
                ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.user_id', $user->id)
                ->where('carts.status', 'active')
                ->groupBy('cart_items.product_id')
                ->selectRaw('cart_items.product_id, SUM(cart_items.quantity) as quantity')
                ->pluck('quantity', 'product_id')
                ->map(fn ($quantity) => (int) $quantity)
                ->all();

        $request->attributes->set($attribute, $quantities);

        return $quantities;
    }

    /**
     * Overlay the current user's cart state onto already resolved product
     * payloads, for lists that are served from a shared cache.
     *
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    public static function withCartState(Request $request, array $products): array
    {
        $quantities = self::cartQuantities($request);

        return array_map(function (array $product) use ($quantities): array {
            $count = $quantities[$product['id']] ?? 0;

            $product['in_cart'] = $count > 0;
            $product['in_cart_count'] = $count;

            return $product;
        }, $products);
    }
}
