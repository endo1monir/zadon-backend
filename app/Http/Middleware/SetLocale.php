<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The locales the application ships translations for.
     *
     * @var list<string>
     */
    public const SUPPORTED_LOCALES = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $locale = $request->is('api/*')
            ? $request->header('lang', $request->query('lang'))
            : $request->session()->get('locale');

        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        return config('app.locale');
    }
}
