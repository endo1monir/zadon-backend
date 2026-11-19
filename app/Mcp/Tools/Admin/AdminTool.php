<?php

namespace App\Mcp\Tools\Admin;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Throwable;

/**
 * Base for the admin MCP tools. Name, description and parameter texts come
 * from `ai.mcp.admin` in config/ai.php; results are compact JSON.
 */
abstract class AdminTool extends Tool
{
    /**
     * The tool's key under `ai.mcp.admin.tools` in config/ai.php.
     */
    abstract protected function key(): string;

    public function name(): string
    {
        return $this->key();
    }

    public function description(): string
    {
        return config("ai.mcp.admin.tools.{$this->key()}.description");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    /**
     * A parameter description: the tool's own, or a shared one (from, to, limit...).
     */
    protected function parameter(string $name): string
    {
        return config("ai.mcp.admin.tools.{$this->key()}.parameters.{$name}")
            ?? config("ai.mcp.admin.common.{$name}");
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function json(array $data): Response
    {
        return Response::text(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    protected function limit(Request $request): int
    {
        $limit = (int) ($request->get('limit') ?: 20);

        return max(1, min($limit, config('ai.mcp.admin.max_results')));
    }

    /**
     * The requested date range, whole days. Defaults to the last 30 days.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function period(Request $request): array
    {
        $to = $this->date($request->get('to')) ?? Carbon::today();
        $from = $this->date($request->get('from')) ?? $to->copy()->subDays(29);

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    protected function boolean(mixed $value): ?bool
    {
        return is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * "Arabic name / English name" of a model with name_ar and name_en.
     */
    protected function nameOf(?Model $model): ?string
    {
        return $model === null ? null : $this->names($model->getAttribute('name_ar'), $model->getAttribute('name_en'));
    }

    protected function names(?string $arabic, ?string $english): ?string
    {
        return implode(' / ', array_unique(array_filter([$arabic, $english]))) ?: null;
    }

    protected function money(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
