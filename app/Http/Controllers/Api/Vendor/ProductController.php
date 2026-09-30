<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\StockAdjustmentRequest;
use App\Http\Requests\Api\Vendor\StoreProductRequest;
use App\Http\Requests\Api\Vendor\UpdateStoreProductRequest;
use App\Http\Resources\StockAdjustmentResource;
use App\Http\Resources\VendorProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $products = $this->managedStore()->products()
            ->with('category')
            ->search($request->string('search')->toString())
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->input('stock') === 'low', fn ($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock_alert'))
            ->when($request->input('stock') === 'out', fn ($q) => $q->where('stock', 0))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return $this->successReturn([
            'products' => VendorProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = $this->managedStore()->products()->create($data + ['stock' => $request->integer('stock', 0)]);

        return $this->successReturn([
            'product' => new VendorProductResource($product->load('category')),
        ], code: 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->managedStore()->products()->findOrFail($product->id)->load('category');

        return $this->successReturn([
            'product' => new VendorProductResource($product),
        ]);
    }

    public function update(UpdateStoreProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->managedStore()->products()->findOrFail($product->id);
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return $this->successReturn([
            'product' => new VendorProductResource($product->load('category')),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->managedStore()->products()->findOrFail($product->id)->delete();

        return $this->successReturn(message: 'messages.product_deleted');
    }

    public function adjustStock(StockAdjustmentRequest $request, Product $product): JsonResponse
    {
        $product = $this->managedStore()->products()->findOrFail($product->id);

        $adjustment = DB::transaction(function () use ($request, $product) {
            $previous = $product->stock;
            $delta = $request->integer('delta');
            $new = max(0, $previous + $delta);

            $product->update(['stock' => $new]);

            return $product->stockAdjustments()->create([
                'store_id' => $product->store_id,
                'type' => $request->input('type', $delta > 0 ? 'restock' : 'correction'),
                'quantity' => $delta,
                'previous_stock' => $previous,
                'new_stock' => $new,
            ]);
        });

        return $this->successReturn([
            'adjustment' => new StockAdjustmentResource($adjustment),
        ], code: 201);
    }

    public function stockHistory(Product $product): JsonResponse
    {
        $product = $this->managedStore()->products()->findOrFail($product->id);

        return $this->successReturn([
            'adjustments' => StockAdjustmentResource::collection(
                $product->stockAdjustments()->latest()->limit(20)->get()
            ),
        ]);
    }
}
