<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\StoreAddressRequest;
use App\Http\Requests\Api\App\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {
        return $this->successReturn([
            'addresses' => AddressResource::collection($request->user()->addresses()->latest()->get()),
        ]);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $request->user()->addresses()->create($request->validated());

        $this->makeDefault($address);

        return $this->successReturn([
            'address' => new AddressResource($address),
        ], code: 201);
    }

    public function update(UpdateAddressRequest $request, int $address): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($address);
        $address->update($request->validated());

        $this->makeDefault($address);

        return $this->successReturn([
            'address' => new AddressResource($address),
        ]);
    }

    public function setDefault(Request $request, int $address): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($address);
        $this->makeDefault($address);

        return $this->successReturn(message: 'messages.address_default_set');
    }

    public function destroy(Request $request, int $address): JsonResponse
    {
        $request->user()->addresses()->findOrFail($address)->delete();

        return $this->successReturn(message: 'messages.address_deleted');
    }

    private function makeDefault(Address $address): void
    {
        $address->update(['is_default' => true]);
        $address->user->addresses()
            ->whereKeyNot($address->id)
            ->update(['is_default' => false]);
    }
}
