<?php

namespace App\Http\Traits;

trait NormalizesPrepTime
{
    /**
     * Accepts a prep time as a plain number or as a `min - max` / `min-max` range
     * (the same shape StoreResource returns) and normalises it to the lower bound.
     */
    protected function normalizePrepTime(string $key): void
    {
        $value = $this->input($key);

        if ($value === null || is_numeric($value)) {
            return;
        }

        if (! is_string($value)) {
            return;
        }

        $min = preg_split('/\s*-\s*/', trim($value))[0] ?? null;

        if ($min !== null && is_numeric($min)) {
            $this->merge([$key => (int) $min]);
        }
    }

    /**
     * @param  array<int, string>  $keys
     */
    protected function normalizePrepTimes(array $keys): void
    {
        foreach ($keys as $key) {
            $this->normalizePrepTime($key);
        }
    }
}
