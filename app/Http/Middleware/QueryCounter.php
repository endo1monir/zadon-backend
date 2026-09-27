<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class QueryCounter
{
    public function handle($request, Closure $next)
    {
        DB::enableQueryLog();

        $response = $next($request);

        $queries = count(DB::getQueryLog());

        $response->headers->set(
            'X-DB-Queries',
            $queries
        );

        return $response;
    }
}
