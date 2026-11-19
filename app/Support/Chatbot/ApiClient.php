<?php

namespace App\Support\Chatbot;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * Runs the app's own customer API endpoints from inside the chatbot, as the
 * same customer (same token and language). Every chatbot action therefore
 * goes through the exact same validation, permission checks and business
 * logic as the mobile app.
 */
class ApiClient
{
    public function __construct(private Application $app) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, message: string, data: mixed, errors: array<string, mixed>}
     */
    public function call(ChatSession $session, string $method, string $uri, array $payload = []): array
    {
        $method = strtoupper($method);
        $isRead = $method === 'GET';

        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_LANG' => $this->app->getLocale(),
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($session->token !== null) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$session->token;
        }

        $subRequest = Request::create(
            '/api/'.ltrim($uri, '/'),
            $method,
            $isRead ? $payload : [],
            server: $server,
            content: $isRead ? null : json_encode($payload),
        );

        $originalRequest = $this->app->make('request');

        try {
            $response = $this->app->make(HttpKernel::class)->handle($subRequest);
        } finally {
            $this->app->instance('request', $originalRequest);
            Facade::clearResolvedInstance('request');
        }

        $body = json_decode((string) $response->getContent(), true);
        $body = is_array($body) ? $body : [];

        return [
            'ok' => $response->isSuccessful() && ($body['success'] ?? true) !== false,
            'status' => $response->getStatusCode(),
            'message' => (string) ($body['message'] ?? ''),
            'data' => $body['data'] ?? null,
            'errors' => $body['errors'] ?? [],
        ];
    }

    /**
     * Turn an API result into the JSON a tool hands back to the model.
     *
     * @param  array{ok: bool, status: int, message: string, data: mixed, errors: array<string, mixed>}  $result
     * @param  array<string, mixed>  $extra  Data to show the model when the call succeeded.
     */
    public static function toolResult(array $result, array $extra = []): string
    {
        if ($result['ok']) {
            return json_encode(array_filter([
                'done' => true,
                'message' => $result['message'],
                ...$extra,
            ], fn (mixed $value): bool => $value !== '' && $value !== null), JSON_UNESCAPED_UNICODE);
        }

        return json_encode(array_filter([
            'done' => false,
            'error' => $result['message'] !== '' ? $result['message'] : "The request failed (HTTP {$result['status']}).",
            'validation_errors' => $result['errors'],
        ], fn (mixed $value): bool => $value !== [] && $value !== '' && $value !== null), JSON_UNESCAPED_UNICODE);
    }
}
