<?php

use App\Models\ChatConversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * The chat keeps its own conversations: the first message returns a
 * conversation_id, later messages send it back, and /chat/history/{id}
 * returns everything that was said.
 */
beforeEach(function () {
    config([
        'ai.provider' => 'gemini',
        'ai.model' => 'gemini-flash-lite-latest',
        'prism.providers.gemini.api_key' => 'test-key',
    ]);
});

test('a guest\'s first message starts a conversation and saves both messages', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiText('أهلاً بيك'))]);

    $conversationId = $this->postJson('/api/chat', ['message' => 'السلام عليكم'])
        ->assertOk()
        ->assertJsonPath('data.reply', 'أهلاً بيك')
        ->json('data.conversation_id');

    expect(Str::isUuid($conversationId))->toBeTrue();

    $conversation = ChatConversation::find($conversationId);

    expect($conversation->user_id)->toBeNull()
        ->and($conversation->messages->map->only('role', 'content')->all())->toBe([
            ['role' => 'user', 'content' => 'السلام عليكم'],
            ['role' => 'assistant', 'content' => 'أهلاً بيك'],
        ]);
});

test('sending the conversation id continues the same conversation', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiText('first answer'))
        ->push(geminiText('second answer')),
    ]);

    $conversationId = $this->postJson('/api/chat', ['message' => 'first question'])->json('data.conversation_id');

    $this->postJson('/api/chat', ['message' => 'second question', 'conversation_id' => $conversationId])
        ->assertOk()
        ->assertJsonPath('data.conversation_id', $conversationId);

    expect(ChatConversation::count())->toBe(1)
        ->and(ChatConversation::find($conversationId)->messages)->toHaveCount(4)
        ->and(geminiRequestBody(1))->toContain('first question')->toContain('first answer')->toContain('second question');
});

test('without a conversation id every message starts a new conversation', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiText('hi'))]);

    $first = $this->postJson('/api/chat', ['message' => 'zebra-first-message'])->json('data.conversation_id');
    $second = $this->postJson('/api/chat', ['message' => 'zebra-second-message'])->json('data.conversation_id');

    expect($first)->not->toBe($second)
        ->and(geminiRequestBody(1))->not->toContain('zebra-first-message');
});

test('only the last messages of a long conversation are sent to the model', function () {
    config(['ai.chat.max_history' => 2]);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiText('ok'))]);

    $conversation = ChatConversation::create();
    $conversation->messages()->create(['role' => 'user', 'content' => 'very old question']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'very old answer']);
    $conversation->messages()->create(['role' => 'user', 'content' => 'recent question']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'recent answer']);

    $this->postJson('/api/chat', ['message' => 'new question', 'conversation_id' => $conversation->id])->assertOk();

    expect(geminiRequestBody(0))
        ->not->toContain('very old')
        ->toContain('recent question')
        ->toContain('recent answer')
        ->toContain('new question');
});

test('an unknown conversation id is refused without calling the model', function () {
    Http::fake();

    $this->postJson('/api/chat', ['message' => 'hi', 'conversation_id' => (string) Str::uuid()])
        ->assertNotFound()
        ->assertJsonPath('success', false);

    Http::assertNothingSent();
    expect(ChatConversation::count())->toBe(0);
});

test('nothing is saved when the AI provider fails', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'boom']], 500)]);

    $this->postJson('/api/chat', ['message' => 'hi'])->assertStatus(503);

    expect(ChatConversation::count())->toBe(0);
});

test('a logged-in customer\'s conversation belongs to them', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    Sanctum::actingAs($customer);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiText('hi'))]);

    $conversationId = $this->postJson('/api/chat', ['message' => 'hello'])->json('data.conversation_id');

    expect(ChatConversation::find($conversationId)->user_id)->toBe($customer->id);
});

test('nobody else can continue or read a customer\'s conversation', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $conversation = ChatConversation::create(['user_id' => $owner->id]);
    $conversation->messages()->create(['role' => 'user', 'content' => 'private']);
    Http::fake();

    // As a guest.
    $this->postJson('/api/chat', ['message' => 'hi', 'conversation_id' => $conversation->id])->assertNotFound();
    $this->getJson("/api/chat/history/{$conversation->id}")->assertNotFound();

    // As another customer.
    Sanctum::actingAs(User::factory()->create(['role' => 'customer']));
    $this->postJson('/api/chat', ['message' => 'hi', 'conversation_id' => $conversation->id])->assertNotFound();
    $this->getJson("/api/chat/history/{$conversation->id}")->assertNotFound();

    Http::assertNothingSent();
});

test('a guest conversation becomes the customer\'s when they continue it after logging in', function () {
    $conversation = ChatConversation::create();
    $customer = User::factory()->create(['role' => 'customer']);
    Sanctum::actingAs($customer);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiText('hi'))]);

    $this->postJson('/api/chat', ['message' => 'hello', 'conversation_id' => $conversation->id])
        ->assertOk()
        ->assertJsonPath('data.conversation_id', $conversation->id);

    expect($conversation->fresh()->user_id)->toBe($customer->id);
});

test('history returns every message of the conversation, oldest first, with its product cards', function () {
    $honey = Product::factory()->create(['name_en' => 'Sidr Honey', 'price' => 50, 'original_price' => 100, 'stock' => 5]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiText('أهلاً'))
        ->push(geminiToolCall('list_offers'))
        ->push(geminiText('دي العروض')),
    ]);

    $conversationId = $this->postJson('/api/chat', ['message' => 'السلام عليكم'])->json('data.conversation_id');
    $this->postJson('/api/chat', ['message' => 'ايه العروض؟', 'conversation_id' => $conversationId])->assertOk();

    $this->getJson("/api/chat/history/{$conversationId}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.conversation_id', $conversationId)
        ->assertJsonCount(4, 'data.messages')
        ->assertJsonPath('data.messages.0.role', 'user')
        ->assertJsonPath('data.messages.0.content', 'السلام عليكم')
        ->assertJsonPath('data.messages.1.role', 'assistant')
        ->assertJsonPath('data.messages.1.content', 'أهلاً')
        ->assertJsonCount(0, 'data.messages.1.products')
        ->assertJsonPath('data.messages.2.content', 'ايه العروض؟')
        ->assertJsonPath('data.messages.3.content', 'دي العروض')
        ->assertJsonPath('data.messages.3.products.0.id', $honey->id)
        ->assertJsonStructure(['data' => ['messages' => [['id', 'role', 'content', 'products', 'orders', 'created_at']]]]);
});

test('history shows the customer\'s order cards', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $order = Order::factory()->create(['user_id' => $customer->id]);
    Sanctum::actingAs($customer);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiToolCall('get_my_orders', ['limit' => 1]))
        ->push(geminiText('Your last order.')),
    ]);

    $conversationId = $this->postJson('/api/chat', ['message' => 'آخر طلب؟'])->json('data.conversation_id');

    $this->getJson("/api/chat/history/{$conversationId}")
        ->assertOk()
        ->assertJsonPath('data.messages.1.orders.0.id', $order->id);
});

test('history of an unknown or malformed id returns 404', function (string $id) {
    $this->getJson("/api/chat/history/{$id}")
        ->assertNotFound()
        ->assertJsonPath('success', false);
})->with([
    'unknown uuid' => fn () => (string) Str::uuid(),
    'not a uuid' => 'abc',
]);
