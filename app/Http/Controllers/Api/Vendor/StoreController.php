<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\StoreStatusRequest;
use App\Http\Requests\Api\Vendor\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use Illuminate\Http\JsonResponse;

class StoreController extends BaseController
{
    public function show(): JsonResponse
    {
        return $this->successReturn([
            'store' => new StoreResource($this->managedStore()->load('category', 'city')),
        ]);
    }

    public function update(UpdateStoreRequest $request): JsonResponse
    {
        $store = $this->managedStore();
        $logo = $request->file('store.logo') ?? $request->file('logo');
        $coverImage = $request->file('store.cover_image') ?? $request->file('cover_image');
        $data = $request->safe()->except(['logo', 'cover_image', 'store']);

        if ($logo) {
            $data['logo'] = $logo->store('stores', 'public');
        }

        if ($coverImage) {
            $data['cover_image'] = $coverImage->store('stores', 'public');
        }

        $store->update($data);

        return $this->successReturn([
            'store' => new StoreResource($store->load('category', 'city')),
        ]);
    }

    public function updateStatus(StoreStatusRequest $request): JsonResponse
    {
        $store = $this->managedStore();
        $store->update(['status' => $request->status]);

        return $this->successReturn([
            'store' => new StoreResource($store->load('category', 'city')),
        ]);
    }
}
