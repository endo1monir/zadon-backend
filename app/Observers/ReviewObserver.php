<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        Cache::forget('home:featured_stores');
        Cache::tags(['stores'])->flush();

    }

    public function deleted(Review $review): void
    {
        Cache::forget('home:featured_stores');
        Cache::tags(['stores'])->flush();

    }
}
