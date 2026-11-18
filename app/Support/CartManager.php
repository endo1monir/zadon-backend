<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

/**
 * Adds products to a customer's cart. Shared by the cart endpoint and the chatbot.
 * A cart holds products from one store only.
 */
class CartManager
{
    public function activeCart(User $user): ?Cart
    {
        return Cart::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Whether adding this product would replace a cart from another store.
     */
    public function wouldReplaceCart(User $user, Product $product): bool
    {
        $cart = $this->activeCart($user);

        return $cart !== null && $cart->store_id !== $product->store_id && $cart->items()->exists();
    }

    /**
     * Add the product, starting a new cart when the current one belongs to another store.
     * Stock must be checked by the caller.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function addItem(User $user, Product $product, int $quantity, ?array $options = null): Cart
    {
        $cart = $this->activeCart($user);

        if ($cart && $cart->store_id !== $product->store_id) {
            Cart::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'abandoned']);

            $cart = null;
        }

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'store_id' => $product->store_id,
                'status' => 'active',
            ]);
        }

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'options' => $options,
            ]);
        }

        return $cart;
    }
}
