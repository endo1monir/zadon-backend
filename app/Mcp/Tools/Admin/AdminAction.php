<?php

namespace App\Mcp\Tools\Admin;

use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

/**
 * Base for admin tools that change data. Every change takes two calls:
 *
 * 1. Without `confirmation_code`: nothing changes. The tool checks the
 *    request and returns a summary plus a one-time code (valid 10 minutes).
 * 2. With that code and the same arguments: the change runs and is logged.
 *
 * The code is tied to the admin, the tool and the exact arguments, so a
 * confirmation can never run a different change than the one summarised.
 */
abstract class AdminAction extends AdminTool
{
    /**
     * Check the request. Return an error message, or the summary to show the
     * admin and the closure that makes the change (returning extra result data).
     *
     * @return string|array{0: string, 1: Closure(): array<string, mixed>}
     */
    abstract protected function prepare(Request $request): string|array;

    /**
     * The tool's own parameters; `confirmation_code` is added for every action.
     *
     * @return array<string, mixed>
     */
    abstract protected function parameters(JsonSchema $schema): array;

    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->parameters($schema),
            'confirmation_code' => $schema->string()->description(config('ai.mcp.admin.common.confirmation_code')),
        ];
    }

    public function handle(Request $request): Response
    {
        $prepared = $this->prepare($request);

        if (is_string($prepared)) {
            return $this->json(['done' => false, 'error' => $prepared]);
        }

        [$summary, $change] = $prepared;

        $admin = $request->user();
        $arguments = Arr::except($request->all(), ['confirmation_code']);
        ksort($arguments);
        $fingerprint = hash('sha256', $this->name().'|'.json_encode($arguments));
        $code = Str::upper(trim((string) $request->get('confirmation_code')));

        if ($code === '') {
            $code = Str::upper(Str::random(6));
            Cache::put($this->cacheKey($admin?->getAuthIdentifier(), $code), $fingerprint, now()->addMinutes(10));

            return $this->json([
                'done' => false,
                'needs_confirmation' => true,
                'summary' => $summary,
                'confirmation_code' => $code,
                'next_step' => 'Nothing changed yet. Show this summary to the admin. Only after they clearly agree in a new message, call this tool again with the same arguments and this confirmation_code.',
            ]);
        }

        if (Cache::pull($this->cacheKey($admin?->getAuthIdentifier(), $code)) !== $fingerprint) {
            return $this->json([
                'done' => false,
                'error' => 'This confirmation code is wrong, expired (10 minutes) or was given for different arguments. Call the tool again without confirmation_code to get a new summary.',
            ]);
        }

        $result = $change();

        Log::info('Admin MCP change', [
            'admin_id' => $admin?->getAuthIdentifier(),
            'tool' => $this->name(),
            'arguments' => $arguments,
        ]);

        return $this->json(['done' => true, 'summary' => $summary, ...$result]);
    }

    private function cacheKey(mixed $adminId, string $code): string
    {
        return "mcp:admin-confirmation:{$adminId}:{$code}";
    }
}
