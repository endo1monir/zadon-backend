<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StoreController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {

            $categoryId = $request->filled('category_id') ? $request->integer('category_id'): null;
            $city = $request->filled('city') ? (string) $request->string('city'): null;
            $search = $request->filled('search')? (string) $request->string('search') : null;
            $page = $request->integer('page', 1);

            $cacheKey = 'stores:list:' . md5(json_encode([
                'category_id' => $categoryId,
                'city' => $city,
                'page' => $page,
            ]));


            $getStores = function () use (
                $categoryId,
                $city,
                $search
            ) {
                $stores = Store::query()
                    ->active()
                    ->with('category')
                    ->when(
                        $categoryId,
                        fn ($q) => $q->where(
                            'category_id',
                            $categoryId
                        )
                    )
                    ->when(
                        $city,
                        fn ($q) => $q->where(
                            'city',
                            $city
                        )
                    )
                    ->when(
                        $search,
                        fn ($q) => $q->where(
                            fn ($inner) => $inner
                                ->where(
                                    'name_ar',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'name_en',
                                    'like',
                                    "%{$search}%"
                                )
                        )
                    )
                    ->orderByDesc('rating')
                    ->paginate(20);

                return [
                    'stores' => StoreResource::collection(
                        $stores->items()
                    )->resolve(),

                    'pagination' => [
                        'current_page' => $stores->currentPage(),
                        'last_page' => $stores->lastPage(),
                        'per_page' => $stores->perPage(),
                        'total' => $stores->total(),
                    ],
                ];
            };


            if ($search) {
                $data = $getStores();
            } else {
                $data = Cache::tags(['stores'])->remember(
                    $cacheKey,
                    now()->addMinutes(10),
                    $getStores
                );
            }

            return $this->successReturn($data);

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

       $categoryId = $request->filled('category_id')? $request->integer('category_id')  : null;

       $search = $request->string('search')->toString();

       $page = $request->integer('page', 1);

      $storeData = Cache::tags(['stores'])->remember(
            "store:{$store->id}",
            now()->addHour(),
            function () use ($store) {
                return (new StoreResource($store))
                    ->resolve();
            }
        );

     $categoryMenus = Cache::tags(['products'])->remember(
        "store:{$store->id}:categories",
        now()->addHour(),
        function () use ($store) {
            $categoryIds = $store->products()
                ->purchasable()
                ->distinct()
                ->pluck('category_id')
                ->filter();
            return CategoryResource::collection(
                Category::query()
                    ->whereIn('id', $categoryIds)
                    ->active()
                    ->orderBy('sort_order')
                    ->get()
            )->resolve();
        }
        );

        $productsKey = 'store:'.$store->id.':products:' . md5(json_encode([
            'category_id' => $categoryId,
            'page' => $page,
        ]));

        $productsData = function () use ( $store,$categoryId,$search) {
            $products = $store->products()
                ->with('category')
                ->purchasable()
                ->when(
                    $search !== '',
                    fn ($q) => $q->where(
                        fn ($inner) => $inner
                            ->where(
                                'name_ar',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'name_en',
                                'like',
                                "%{$search}%"
                            )
                    )
                )
                ->when(
                    $categoryId,
                    fn ($q) => $q->where(
                        'category_id',
                        $categoryId
                    )
                )
                ->orderByDesc('sales_count')
                ->paginate(20);


            return [
                'products' => ProductResource::collection(
                    $products->items()
                )->resolve(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ];
    };

        if ($search !== '') {
            $products = $productsData();

        } else {

            $products = Cache::tags([
                'products',
                "store:{$store->id}"
            ])->remember(
                $productsKey,
                now()->addMinutes(10),
                $productsData
            );

        }


        return $this->successReturn([
            'store' => $storeData,

            'category_menus' => $categoryMenus,

            'products' => $products['products'],

            'pagination' => $products['pagination'],
        ]);
    }
}
