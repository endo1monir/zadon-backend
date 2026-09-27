<?php

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function seedPaymentMethod(array $attributes = []): PaymentMethod
{
    return PaymentMethod::create(array_merge([
        'key' => 'card',
        'name_ar' => 'بطاقة',
        'name_en' => 'Card',
        'sort_order' => 1,
    ], $attributes));
}

test('payment methods are listed publicly in sort order', function () {
    seedPaymentMethod(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 3]);
    $card = seedPaymentMethod(['key' => 'card', 'name_en' => 'Card', 'sort_order' => 1]);
    seedPaymentMethod(['key' => 'wallet', 'name_en' => 'Wallet', 'sort_order' => 2]);

    $this->getJson('/api/payment-methods')
        ->assertOk()
        ->assertJsonStructure(['data' => ['payment_methods' => [['id', 'key', 'name', 'icon']]]])
        ->assertJsonPath('data.payment_methods.0.id', $card->id)
        ->assertJsonPath('data.payment_methods.0.key', 'card')
        ->assertJsonCount(3, 'data.payment_methods');
});

test('inactive payment methods are hidden', function () {
    seedPaymentMethod(['key' => 'card', 'sort_order' => 1]);
    seedPaymentMethod(['key' => 'cash', 'name_en' => 'Cash', 'is_active' => false, 'sort_order' => 2]);

    $this->getJson('/api/payment-methods')
        ->assertOk()
        ->assertJsonCount(1, 'data.payment_methods')
        ->assertJsonPath('data.payment_methods.0.key', 'card');
});

test('the payment method name follows the lang header', function () {
    seedPaymentMethod(['key' => 'cash', 'name_ar' => 'الدفع عند الاستلام', 'name_en' => 'Cash on delivery']);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/payment-methods')
        ->assertOk()
        ->assertJsonPath('data.payment_methods.0.name', 'الدفع عند الاستلام');
});

test('the payment method name is returned in english by default', function () {
    seedPaymentMethod(['key' => 'cash', 'name_ar' => 'الدفع عند الاستلام', 'name_en' => 'Cash on delivery']);

    $this->getJson('/api/payment-methods')
        ->assertOk()
        ->assertJsonPath('data.payment_methods.0.name', 'Cash on delivery');
});

test('the payment method icon is returned as a full path', function () {
    seedPaymentMethod(['icon' => 'payment-methods/card.png']);

    $this->getJson('/api/payment-methods')
        ->assertOk()
        ->assertJsonPath('data.payment_methods.0.icon', fn ($icon) => str_contains($icon, '/storage/payment-methods/card.png'));
});

test('an authenticated user can upload a payment method icon', function () {
    Storage::fake('public');

    $paymentMethod = seedPaymentMethod();
    Sanctum::actingAs(User::create(['name' => 'مدير', 'phone' => '0559999999', 'role' => 'customer']), ['app']);

    $this->post("/api/payment-methods/{$paymentMethod->id}/icon", [
        'icon' => UploadedFile::fake()->create('card.png', 100, 'image/png'),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Payment method icon uploaded successfully.')
        ->assertJsonPath('data.payment_method.icon', fn ($icon) => str_contains($icon, '/storage/payment-methods/'));

    Storage::disk('public')->assertExists($paymentMethod->refresh()->icon);
});

test('uploading a payment method icon requires authentication', function () {
    $paymentMethod = seedPaymentMethod();

    $this->postJson("/api/payment-methods/{$paymentMethod->id}/icon", ['icon' => 'x'])->assertStatus(401);
});
