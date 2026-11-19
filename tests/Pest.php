<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Authenticate the current test as an admin and return the created user.
 */
function actingAsAdmin(): User
{
    $admin = User::factory()->create(['role' => 'admin']);

    test()->actingAs($admin);

    return $admin;
}

/**
 * A fake Gemini response in which the model calls one tool.
 *
 * @param  array<string, mixed>  $args
 * @return array<string, mixed>
 */
function geminiToolCall(string $name, array $args = []): array
{
    return [
        'candidates' => [[
            'content' => ['role' => 'model', 'parts' => [['functionCall' => ['name' => $name, 'args' => (object) $args]]]],
            'finishReason' => 'STOP',
        ]],
        'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 5],
    ];
}

/**
 * A fake Gemini response in which the model answers with text.
 *
 * @return array<string, mixed>
 */
function geminiText(string $text): array
{
    return [
        'candidates' => [[
            'content' => ['role' => 'model', 'parts' => [['text' => $text]]],
            'finishReason' => 'STOP',
        ]],
        'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 5],
    ];
}

/**
 * Body of the Nth request sent to Gemini (0-based), as a JSON string.
 */
function geminiRequestBody(int $index): string
{
    $requests = Http::recorded(fn (HttpRequest $request): bool => str_contains($request->url(), 'generativelanguage.googleapis.com'));

    return json_encode($requests[$index][0]->data(), JSON_UNESCAPED_UNICODE);
}
