<?php

use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => actingAsAdmin());

describe('payment methods', function () {
    it('lists payment methods', function () {
        PaymentMethod::factory()->create(['name_ar' => 'الدفع عند الاستلام', 'name_en' => 'Cash', 'key' => 'cash']);

        $this->get(route('admin.payment-methods.index'))
            ->assertOk()
            ->assertSee('الدفع عند الاستلام')
            ->assertSee('Cash');
    });

    it('searches payment methods', function () {
        PaymentMethod::factory()->create(['name_en' => 'Cash', 'key' => 'cash']);
        PaymentMethod::factory()->create(['name_en' => 'Visa', 'key' => 'card']);

        $this->get(route('admin.payment-methods.index', ['search' => 'Visa']))
            ->assertOk()
            ->assertSee('Visa')
            ->assertDontSee('Cash');
    });

    it('creates a payment method', function () {
        $this->post(route('admin.payment-methods.store'), [
            'key' => 'apple_pay',
            'name_ar' => 'آبل باي',
            'name_en' => 'Apple Pay',
            'sort_order' => 2,
            'is_active' => '1',
        ])->assertRedirect(route('admin.payment-methods.index'));

        expect(PaymentMethod::where('key', 'apple_pay')->exists())->toBeTrue();
    });

    it('requires a unique key', function () {
        PaymentMethod::factory()->create(['key' => 'cash']);

        $this->post(route('admin.payment-methods.store'), [
            'key' => 'cash',
            'name_ar' => 'نقدا',
            'name_en' => 'Cash again',
        ])->assertSessionHasErrors('key');
    });

    it('requires the english name', function () {
        $this->post(route('admin.payment-methods.store'), [
            'key' => 'wallet',
            'name_ar' => 'المحفظة',
        ])->assertSessionHasErrors('name_en');
    });

    it('rejects a non alpha dash key', function () {
        $this->post(route('admin.payment-methods.store'), [
            'key' => 'apple pay!',
            'name_ar' => 'آبل باي',
            'name_en' => 'Apple Pay',
        ])->assertSessionHasErrors('key');
    });

    it('uploads an icon', function () {
        Storage::fake('public');

        $this->post(route('admin.payment-methods.store'), [
            'key' => 'wallet',
            'name_ar' => 'المحفظة',
            'name_en' => 'Wallet',
            'icon' => UploadedFile::fake()->create('wallet.png', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.payment-methods.index'));

        $path = PaymentMethod::where('key', 'wallet')->value('icon');

        expect($path)->not->toBeNull();
        Storage::disk('public')->assertExists($path);
    });

    it('replaces an icon and deletes the old file', function () {
        Storage::fake('public');
        $paymentMethod = PaymentMethod::factory()->create();
        $paymentMethod->update(['icon' => 'payment-methods/old.png']);
        Storage::disk('public')->put('payment-methods/old.png', 'x');

        $this->put(route('admin.payment-methods.update', $paymentMethod), [
            'key' => $paymentMethod->key,
            'name_ar' => $paymentMethod->name_ar,
            'name_en' => $paymentMethod->name_en,
            'icon' => UploadedFile::fake()->create('new.png', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.payment-methods.index'));

        $newPath = $paymentMethod->refresh()->icon;

        expect($newPath)->not->toBe('payment-methods/old.png');
        Storage::disk('public')->assertMissing('payment-methods/old.png');
        Storage::disk('public')->assertExists($newPath);
    });

    it('keeps the existing icon when no new file is uploaded', function () {
        $paymentMethod = PaymentMethod::factory()->create(['icon' => 'payment-methods/keep.png']);

        $this->put(route('admin.payment-methods.update', $paymentMethod), [
            'key' => $paymentMethod->key,
            'name_ar' => 'محدث',
            'name_en' => $paymentMethod->name_en,
        ])->assertRedirect(route('admin.payment-methods.index'));

        expect($paymentMethod->refresh()->icon)->toBe('payment-methods/keep.png');
    });

    it('allows keeping its own key on update', function () {
        $paymentMethod = PaymentMethod::factory()->create(['key' => 'cash']);

        $this->put(route('admin.payment-methods.update', $paymentMethod), [
            'key' => 'cash',
            'name_ar' => 'محدث',
            'name_en' => 'Updated',
        ])->assertSessionHasNoErrors();

        expect($paymentMethod->refresh()->name_ar)->toBe('محدث');
    });

    it('toggles a payment method status', function () {
        $paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);

        $this->patch(route('admin.payment-methods.toggle', $paymentMethod))->assertRedirect();

        expect($paymentMethod->refresh()->is_active)->toBeFalse();
    });

    it('deletes a payment method and its icon', function () {
        Storage::fake('public');
        $paymentMethod = PaymentMethod::factory()->create(['icon' => 'payment-methods/gone.png']);
        Storage::disk('public')->put('payment-methods/gone.png', 'x');

        $this->delete(route('admin.payment-methods.destroy', $paymentMethod))->assertRedirect();

        expect(PaymentMethod::whereKey($paymentMethod->id)->exists())->toBeFalse();
        Storage::disk('public')->assertMissing('payment-methods/gone.png');
    });
});

describe('settings', function () {
    it('lists settings', function () {
        Setting::factory()->create(['key' => 'policy_ar', 'value' => 'نص الخصوصية']);

        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('policy_ar')
            ->assertSee('نص الخصوصية');
    });

    it('creates a setting', function () {
        $this->post(route('admin.settings.store'), [
            'key' => 'policy_en',
            'value' => 'Privacy text',
        ])->assertRedirect(route('admin.settings.index'));

        expect(Setting::where('key', 'policy_en')->value('value'))->toBe('Privacy text');
    });

    it('requires a unique key', function () {
        Setting::factory()->create(['key' => 'policy_ar']);

        $this->post(route('admin.settings.store'), ['key' => 'policy_ar', 'value' => 'x'])
            ->assertSessionHasErrors('key');
    });

    it('requires a value', function () {
        $this->post(route('admin.settings.store'), ['key' => 'policy_en'])
            ->assertSessionHasErrors('value');
    });

    it('updates a setting', function () {
        $setting = Setting::factory()->create(['key' => 'policy_ar', 'value' => 'old']);

        $this->put(route('admin.settings.update', $setting), [
            'key' => 'policy_ar',
            'value' => 'new text',
        ])->assertRedirect(route('admin.settings.index'));

        expect($setting->refresh()->value)->toBe('new text');
    });

    it('deletes a setting', function () {
        $setting = Setting::factory()->create();

        $this->delete(route('admin.settings.destroy', $setting))->assertRedirect();

        expect(Setting::whereKey($setting->id)->exists())->toBeFalse();
    });
});

describe('banners', function () {
    it('lists banners', function () {
        Banner::factory()->create(['image' => 'banners/one.jpg']);

        $this->get(route('admin.banners.index'))
            ->assertOk()
            ->assertSee('banners/one.jpg');
    });

    it('uploads a banner', function () {
        Storage::fake('public');

        $this->post(route('admin.banners.store'), [
            'image' => UploadedFile::fake()->create('banner.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.banners.index'));

        $path = Banner::latest('id')->value('image');

        expect($path)->not->toBeNull();
        Storage::disk('public')->assertExists($path);
    });

    it('requires an image on create', function () {
        $this->post(route('admin.banners.store'), [])->assertSessionHasErrors('image');
    });

    it('rejects a non image upload', function () {
        Storage::fake('public');

        $this->post(route('admin.banners.store'), [
            'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('image');
    });

    it('replaces a banner image', function () {
        Storage::fake('public');
        $banner = Banner::factory()->create(['image' => 'banners/old.jpg']);
        Storage::disk('public')->put('banners/old.jpg', 'x');

        $this->put(route('admin.banners.update', $banner), [
            'image' => UploadedFile::fake()->create('new.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.banners.index'));

        $path = $banner->refresh()->image;

        expect($path)->not->toBe('banners/old.jpg');
        Storage::disk('public')->assertMissing('banners/old.jpg');
        Storage::disk('public')->assertExists($path);
    });

    it('deletes a banner and its image', function () {
        Storage::fake('public');
        $banner = Banner::factory()->create(['image' => 'banners/gone.jpg']);
        Storage::disk('public')->put('banners/gone.jpg', 'x');

        $this->delete(route('admin.banners.destroy', $banner))->assertRedirect();

        expect(Banner::whereKey($banner->id)->exists())->toBeFalse();
        Storage::disk('public')->assertMissing('banners/gone.jpg');
    });
});

describe('contact messages', function () {
    it('lists messages from guests and users', function () {
        ContactMessage::factory()->create(['title' => 'Guest question', 'user_id' => null]);
        ContactMessage::factory()->fromUser()->create(['title' => 'User question']);

        $this->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Guest question')
            ->assertSee('User question')
            ->assertSee('Guest');
    });

    it('filters messages by registered sender', function () {
        ContactMessage::factory()->create(['title' => 'Guest question', 'user_id' => null]);
        ContactMessage::factory()->fromUser()->create(['title' => 'User question']);

        $this->get(route('admin.contact-messages.index', ['from_user' => '1']))
            ->assertOk()
            ->assertSee('User question')
            ->assertDontSee('Guest question');
    });

    it('shows a single message', function () {
        $message = ContactMessage::factory()->create([
            'title' => 'Order issue',
            'message' => 'My order never arrived.',
        ]);

        $this->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('Order issue')
            ->assertSee('My order never arrived.');
    });

    it('deletes a message', function () {
        $message = ContactMessage::factory()->create();

        $this->delete(route('admin.contact-messages.destroy', $message))->assertRedirect();

        expect(ContactMessage::whereKey($message->id)->exists())->toBeFalse();
    });

    it('does not expose create or edit routes for messages', function () {
        expect(Route::has('admin.contact-messages.create'))->toBeFalse()
            ->and(Route::has('admin.contact-messages.edit'))->toBeFalse();
    });
});
