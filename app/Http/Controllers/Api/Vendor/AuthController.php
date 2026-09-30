<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\VendorLoginRequest;
use App\Http\Requests\Api\Vendor\VendorRegisterRequest;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function register(VendorRegisterRequest $request): JsonResponse
    {
        $logo = $request->file('store.logo');
        $coverImage = $request->file('store.cover_image');

        [$user, $store] = DB::transaction(function () use ($request, $logo, $coverImage): array {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'vendor',
            ]);

            $storeData = Arr::except($request->validated('store'), ['logo', 'cover_image']);

            if ($logo) {
                $storeData['logo'] = $logo->store('stores', 'public');
            }

            if ($coverImage) {
                $storeData['cover_image'] = $coverImage->store('stores', 'public');
            }

            $store = $user->stores()->create($storeData + ['is_verified' => true]);

            return [$user, $store];
        });

        $store->refresh()->load('category', 'city');

        return $this->successReturn([
            'token' => $user->createToken('vendor', ['vendor'])->plainTextToken,
            'user' => new UserResource($user->load('city')),
            'stores' => StoreResource::collection($user->stores()->with('category', 'city')->get()),
            'store' => new StoreResource($store->load('category', 'city')),
        ], code: 201);
    }

    public function login(VendorLoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || $user->role !== 'vendor' || ! Hash::check($request->password, $user->password)) {
            return $this->failReturn('auth.failed');
        }

        $accessToken = $user->createToken('vendor', ['vendor']);
        $token = $accessToken->plainTextToken;

        if ($request->filled('fcm_token')) {
            $accessToken->accessToken->forceFill(['fcm_token' => $request->validated('fcm_token')])->save();
        }

        return $this->successReturn([
            'token' => $token,
            'fcm_token' => $accessToken->accessToken->fcm_token,
            'user' => new UserResource($user->load('city')),
            'stores' => StoreResource::collection($user->stores()->with('category', 'city')->get()),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successReturn([
            'user' => new UserResource($user->load('city')),
            'store' => new StoreResource($this->managedStore()->load('category', 'city')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successReturn(message: 'messages.logged_out');
    }
}
