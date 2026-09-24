<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\VendorLoginRequest;
use App\Http\Requests\Api\Vendor\VendorRegisterRequest;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function register(VendorRegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'vendor',
        ]);

        $store = $user->stores()->create($request->input('store') + ['is_verified' => true]);

        return $this->successReturn([
            'token' => $user->createToken('vendor', ['vendor'])->plainTextToken,
            'user' => new UserResource($user->load('city')),
            'stores' => StoreResource::collection($user->stores()->get()),
            'store' => new StoreResource($store),
        ], code: 201);
    }

    public function login(VendorLoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || $user->role !== 'vendor' || ! Hash::check($request->password, $user->password)) {
            return $this->failReturn('auth.failed');
        }

        return $this->successReturn([
            'token' => $user->createToken('vendor', ['vendor'])->plainTextToken,
            'user' => new UserResource($user->load('city')),
            'stores' => StoreResource::collection($user->stores()->get()),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successReturn([
            'user' => new UserResource($user->load('city')),
            'store' => new StoreResource($this->managedStore()->load('category')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successReturn(message: 'messages.logged_out');
    }
}
