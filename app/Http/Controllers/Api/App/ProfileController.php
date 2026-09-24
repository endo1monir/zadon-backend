<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use ResponseTrait;

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return $this->successReturn([
            'user' => new UserResource($user->load('city')),
        ]);
    }
}
