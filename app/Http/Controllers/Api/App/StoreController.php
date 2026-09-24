<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Store::query()
            ->active()
            ->with('category')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->string('city')))
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('name_ar', 'like', "%{$request->string('search')}%")
                    ->orWhere('name_en', 'like', "%{$request->string('search')}%"))
            );

        $stores = $query->orderByDesc('rating')->paginate(20)->withQueryString();

        return $this->successReturn([
            'stores' => StoreResource::collection($stores),
            'pagination' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ],
        ]);
    }

    public function show(Store $store): JsonResponse
    {
        abort_unless($store->is_active, 404);

        $store->load('category');

        return $this->successReturn([
            'store' => new StoreResource($store),
        ]);
    }

    public function products(Store $store, Request $request): JsonResponse
    {
        abort_unless($store->is_active, 404);

        $products = $store->products()
            ->with('category')
            ->purchasable()
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('name_ar', 'like', "%{$request->string('search')}%")
                    ->orWhere('name_en', 'like', "%{$request->string('search')}%"))
            )
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->orderByDesc('sales_count')
            ->paginate(20)
            ->withQueryString();

        return $this->successReturn([
            'store' => new StoreResource($store),
            'products' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
