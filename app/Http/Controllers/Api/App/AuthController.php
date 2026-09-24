<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\CompleteProfileRequest;
use App\Http\Requests\Api\App\LoginRequest;
use App\Http\Requests\Api\App\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ResponseTrait;

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => null, 'role' => 'customer', 'is_completed' => false],
        );

        $user->code = (string) random_int(1000, 9999);
        $user->save();

        Log::info('OTP sent', ['phone' => $user->phone, 'code' => $user->code]);

        return $this->successReturn([
            'code' => app()->isProduction() ? null : $user->code,
            'user' => new UserResource($user->load('city')),
        ], 'messages.otp_sent');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || $user->code !== $request->otp) {
            return $this->failReturn('auth.failed');
        }

        $user->update(['code' => null, 'phone_verified_at' => now()]);

        if (! $user->wallet) {
            $user->wallet()->save(new Wallet(['balance' => 0]));
        }

        $accessToken = $user->createToken('auth-token');
        $token = $accessToken->plainTextToken;

        if ($request->filled('fcm_token')) {
            $accessToken->accessToken->forceFill(['fcm_token' => $request->validated('fcm_token')])->save();
        }

        return $this->successReturn([
            'token' => $token,
            'fcm_token' => $accessToken->accessToken->fcm_token,
            'user' => new UserResource($user->load('wallet', 'city')),
            'is_completed' => (bool) $user->is_completed,
        ]);
    }

    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['avatar']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $request->user()->update($data + ['is_completed' => true]);

        return $this->successReturn([
            'user' => new UserResource($request->user()->load('wallet', 'city')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successReturn([
            'user' => new UserResource($request->user()->load('wallet', 'city')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successReturn(data: null, message: 'messages.logged_out');
    }
}
