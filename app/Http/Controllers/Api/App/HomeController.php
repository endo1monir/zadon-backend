<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        return $this->successReturn([
            'banners' => BannerResource::collection(
                Banner::query()->orderBy('id')->get()
            ),
            'categories' => CategoryResource::collection(
                Category::query()->active()->whereNull('parent_id')->orderBy('sort_order')->get()
            ),
            'featured_stores' => StoreResource::collection(
                Store::query()->active()->with('category')->orderByDesc('rating')->limit(5)->get()
            ),
            // 'featured_products' => ProductResource::collection(
            //     Product::query()->with('store', 'category')->purchasable()->orderByDesc('sales_count')->limit(10)->get()
            // ),
        ]);
    }
}
