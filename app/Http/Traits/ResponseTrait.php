<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function successReturn(mixed $data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message !== '' ? trans($message) : '',
            'data' => $data,
        ], $code);
    }

    public function failReturn(string $message = '', mixed $data = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message !== '' ? trans($message) : '',
            'data' => $data,
        ], $code);
    }
}
