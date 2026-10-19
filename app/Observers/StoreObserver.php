<?php

namespace App\Observers;

use App\Models\Store;
use App\Support\CacheGroup;
use Illuminate\Support\Facades\Cache;

class StoreObserver
{
    public function saved(Store $store): void
    {
        Cache::forget('home:featured_stores');
        CacheGroup::flush('stores');
    }

    public function deleted(Store $store): void
    {
        Cache::forget('home:featured_stores');
        CacheGroup::flush('stores');

    }
}
