<?php

namespace App\Observers;

use App\Models\Review;
use App\Support\CacheGroup;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        Cache::forget('home:featured_stores');
        CacheGroup::flush('stores');

    }

    public function deleted(Review $review): void
    {
        Cache::forget('home:featured_stores');
        CacheGroup::flush('stores');

    }
}
