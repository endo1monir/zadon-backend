<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Traits\ResponseTrait;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $cities = City::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return $this->successReturn([
            'cities' => CityResource::collection($cities),
        ]);
    }
}
