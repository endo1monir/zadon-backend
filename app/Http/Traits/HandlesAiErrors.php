<?php

namespace App\Http\Traits;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismProviderOverloadedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;

/**
 * Turns AI provider failures into clean JSON errors. Requires ResponseTrait.
 */
trait HandlesAiErrors
{
    /**
     * @param  Closure(): JsonResponse  $callback
     */
    protected function callAi(Closure $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (PrismRateLimitedException $exception) {
            return $this->failReturn('messages.ai_rate_limited', code: 429);
        } catch (PrismProviderOverloadedException $exception) {
            return $this->failReturn('messages.ai_rate_limited', code: 503);
        } catch (PrismException|ConnectionException $exception) {
            report($exception);

            return $this->failReturn('messages.ai_unavailable', code: 503);
        }
    }
}
