<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\SocialIconRequest;
use App\Http\Resources\SocialResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Social;
use Illuminate\Http\JsonResponse;

class SocialController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $socials = Social::query()
            ->orderBy('id')
            ->get();

        return $this->successReturn([
            'socials' => SocialResource::collection($socials),
        ]);
    }

    public function uploadIcon(SocialIconRequest $request, Social $social): JsonResponse
    {
        $icon = $request->file('icon')->store('socials', 'public');

        $social->update(['icon' => $icon]);

        return $this->successReturn([
            'social' => new SocialResource($social),
        ], 'messages.social_icon_uploaded');
    }
}
