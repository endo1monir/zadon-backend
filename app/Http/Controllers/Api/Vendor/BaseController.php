<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Store;
use App\Models\User;

abstract class BaseController extends Controller
{
    use ResponseTrait;

    protected function user(): User
    {
        return auth()->user();
    }

    protected function managedStore(): Store
    {
        return Store::query()
            ->whereBelongsTo($this->user(), 'owner')
            ->when(
                request()->filled('store_id'),
                fn ($query) => $query->whereKey(request()->integer('store_id'))
            )
            ->orderBy('id')
            ->firstOrFail();
    }
}
