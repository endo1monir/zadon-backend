<?php

namespace App\Observers;

use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class StoreObserver
{
    public function saved(Store $store): void
    {
        Cache::forget('home:featured_stores');
        Cache::tags(['stores'])->flush();
    }

    public function deleted(Store $store): void
    {
        Cache::forget('home:featured_stores');
        Cache::tags(['stores'])->flush();

    }
}
