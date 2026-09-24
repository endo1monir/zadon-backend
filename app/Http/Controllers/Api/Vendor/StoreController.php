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
            'store' => new StoreResource($this->managedStore()->load('category')),
        ]);
    }

    public function update(UpdateStoreRequest $request): JsonResponse
    {
        $store = $this->managedStore();
        $data = $request->safe()->except(['logo', 'cover_image']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('stores', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('stores', 'public');
        }

        $store->update($data);

        return $this->successReturn([
            'store' => new StoreResource($store->load('category')),
        ]);
    }

    public function updateStatus(StoreStatusRequest $request): JsonResponse
    {
        $store = $this->managedStore();
        $store->update(['status' => $request->status]);

        return $this->successReturn([
            'store' => new StoreResource($store),
        ]);
    }
}
