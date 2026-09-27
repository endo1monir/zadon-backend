<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Review;
use App\Models\Store;
use App\Observers\BannerObserver;
use App\Observers\CategoryObserver;
use App\Observers\ReviewObserver;
use App\Observers\StoreObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Banner::observe(BannerObserver::class);
        Category::observe(CategoryObserver::class);
        Store::observe(StoreObserver::class);
        Review::observe(ReviewObserver::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(10)->by(
                $request->input('phone').'|'.$request->ip()
            );
        });
    }
}
