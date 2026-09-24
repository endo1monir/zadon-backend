<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class PolicyController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $locale = app()->getLocale();
        $content = Setting::value($locale === 'ar' ? 'policy_ar' : 'policy_en');

        if ($content === null) {
            return $this->failReturn('messages.policy_not_found');
        }

        return $this->successReturn([
            'policy' => [
                'language' => $locale,
                'content' => $content,
            ],
        ]);
    }
}
