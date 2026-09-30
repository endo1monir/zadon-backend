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
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {

       //before caching
        // return $this->successReturn([
        //     'banners' => BannerResource::collection(
        //         Banner::query()->orderBy('id')->get()
        //     ),
        //     'categories' => CategoryResource::collection(
        //         Category::query()->active()->whereNull('parent_id')->orderBy('sort_order')->get()
        //     ),
        //     'featured_stores' => StoreResource::collection(
        //         Store::query()->active()->with('category', 'city')->orderByDesc('rating')->limit(5)->get()
        //     ),
        //             ]);

        //after caching

        $banners = Cache::remember(
            'home:banners',
            now()->addHours(6),
            fn () => Banner::query()
                ->orderBy('id')
                ->get()
        );

        $categories = Cache::remember(
            'home:categories',
            now()->addHours(6),
            fn () => Category::query()
                ->active()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get()
        );

        $featuredStores = Cache::remember(
            'home:featured_stores',
            now()->addMinutes(30),
            fn () => Store::query()
                ->active()
                ->with('category', 'city')
                ->orderByDesc('rating')
                ->limit(5)
                ->get()
        );

        return $this->successReturn([
            'banners' => BannerResource::collection($banners),
            'categories' => CategoryResource::collection($categories),
            'featured_stores' => StoreResource::collection($featuredStores),
        ]);
    }

}
