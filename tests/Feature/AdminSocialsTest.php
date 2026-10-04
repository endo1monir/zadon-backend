<?php

use App\Http\Resources\SocialResource;
use App\Models\Social;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = actingAsAdmin();
});

/**
 * @return array<string, mixed>
 */
function socialPayload(array $overrides = []): array
{
    return [
        'name_ar' => 'انستقرام',
        'name_en' => 'Instagram',
        'link' => 'https://instagram.com/zadon_sa',
        ...$overrides,
    ];
}

it('requires authentication', function (): void {
    auth()->logout();

    $this->get(route('admin.socials.index'))->assertRedirect(route('admin.login'));
});

it('rejects non admin users', function (): void {
    $this->actingAs(User::factory()->create(['role' => 'customer']));

    $this->get(route('admin.socials.index'))->assertForbidden();
    $this->post(route('admin.socials.store'), socialPayload())->assertForbidden();
});

it('lists social links', function (): void {
    Social::factory()->create([
        'name_ar' => 'انستقرام',
        'name_en' => 'Instagram',
        'link' => 'https://instagram.com/zadon_sa',
    ]);

    $this->get(route('admin.socials.index'))
        ->assertOk()
        ->assertSee('انستقرام')
        ->assertSee('Instagram')
        ->assertSee('https://instagram.com/zadon_sa');
});

it('exposes the tab in the sidebar', function (): void {
    $this->get(route('admin.socials.index'))->assertOk()->assertSee('Social Links');
});

it('searches by name and link', function (): void {
    $match = Social::factory()->create(['name_ar' => 'انستقرام', 'link' => 'https://instagram.com/zadon_sa']);
    Social::factory()->create(['name_ar' => 'تيك توك', 'link' => 'https://tiktok.com/@zadon_sa']);

    $this->get(route('admin.socials.index', ['search' => 'انستقرام']))
        ->assertOk()
        ->assertSee($match->name_ar)
        ->assertDontSee('تيك توك');

    $this->get(route('admin.socials.index', ['search' => 'tiktok']))
        ->assertOk()
        ->assertSee('تيك توك')
        ->assertDontSee($match->name_ar);
});

it('shows the create form', function (): void {
    $this->get(route('admin.socials.create'))->assertOk()->assertSee('Add social link');
});

it('shows the edit form', function (): void {
    $social = Social::factory()->create(['name_ar' => 'انستقرام']);

    $this->get(route('admin.socials.edit', $social))
        ->assertOk()
        ->assertSee('Edit social link')
        ->assertSee('انستقرام');
});

it('creates a social link', function (): void {
    $this->post(route('admin.socials.store'), socialPayload())
        ->assertRedirect(route('admin.socials.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('socials', [
        'name_ar' => 'انستقرام',
        'name_en' => 'Instagram',
        'link' => 'https://instagram.com/zadon_sa',
    ]);
});

it('allows an english name to be omitted', function (): void {
    $this->post(route('admin.socials.store'), socialPayload(['name_en' => null]))
        ->assertSessionHas('success');

    expect(Social::sole()->name_en)->toBeNull();
});

it('requires an arabic name and a valid link', function (): void {
    $this->post(route('admin.socials.store'), ['name_ar' => '', 'link' => 'not-a-url'])
        ->assertSessionHasErrors(['name_ar', 'link']);

    expect(Social::count())->toBe(0);
});

it('rejects a link without a scheme', function (): void {
    $this->post(route('admin.socials.store'), socialPayload(['link' => 'instagram.com/zadon']))
        ->assertSessionHasErrors('link');
});

it('uploads an icon', function (): void {
    Storage::fake('public');

    $this->post(route('admin.socials.store'), socialPayload([
        'icon' => UploadedFile::fake()->create('icon.jpg', 120, 'image/jpeg'),
    ]))->assertSessionHas('success');

    $social = Social::sole();

    expect($social->icon)->not->toBeNull();

    Storage::disk('public')->assertExists($social->icon);
});

it('replaces the icon and removes the old file', function (): void {
    Storage::fake('public');

    $social = Social::factory()->create(['icon' => 'socials/old.jpg']);
    Storage::disk('public')->put('socials/old.jpg', 'old');

    $this->put(route('admin.socials.update', $social), socialPayload([
        'icon' => UploadedFile::fake()->create('new.png', 80, 'image/png'),
    ]))->assertSessionHas('success');

    $social->refresh();

    expect($social->icon)->not->toBe('socials/old.jpg');

    Storage::disk('public')->assertMissing('socials/old.jpg');
    Storage::disk('public')->assertExists($social->icon);
});

it('keeps the existing icon when no new file is uploaded', function (): void {
    Storage::fake('public');

    $social = Social::factory()->create(['icon' => 'socials/keep.jpg']);
    Storage::disk('public')->put('socials/keep.jpg', 'keep');

    $this->put(route('admin.socials.update', $social), socialPayload(['name_en' => 'Renamed']))
        ->assertSessionHas('success');

    expect($social->refresh()->icon)->toBe('socials/keep.jpg');

    Storage::disk('public')->assertExists('socials/keep.jpg');
});

it('rejects a non image icon', function (): void {
    Storage::fake('public');

    $this->post(route('admin.socials.store'), socialPayload([
        'icon' => UploadedFile::fake()->create('payload.pdf', 10, 'application/pdf'),
    ]))->assertSessionHasErrors('icon');

    expect(Social::count())->toBe(0);
});

it('updates a social link', function (): void {
    $social = Social::factory()->create(['name_ar' => 'قديم']);

    $this->put(route('admin.socials.update', $social), socialPayload(['name_ar' => 'جديد']))
        ->assertRedirect(route('admin.socials.index'))
        ->assertSessionHas('success');

    expect($social->refresh()->name_ar)->toBe('جديد');
});

it('deletes a social link and its icon', function (): void {
    Storage::fake('public');

    $social = Social::factory()->create(['icon' => 'socials/gone.jpg']);
    Storage::disk('public')->put('socials/gone.jpg', 'gone');

    $this->delete(route('admin.socials.destroy', $social))
        ->assertSessionHas('success');

    expect(Social::count())->toBe(0);

    Storage::disk('public')->assertMissing('socials/gone.jpg');
});

it('renders an empty state', function (): void {
    $this->get(route('admin.socials.index'))->assertOk()->assertSee('No social links found');
});

it('returns the complete icon path from the api resource', function (): void {
    $social = Social::factory()->create([
        'name_ar' => 'انستقرام',
        'name_en' => 'Instagram',
        'icon' => 'socials/instagram.png',
    ]);

    $resource = (new SocialResource($social))->toArray(request());

    expect($resource['icon'])->toBe(asset('storage/socials/instagram.png'))
        ->and($resource['icon'])->toStartWith('http')
        ->and($resource['name_ar'])->toBe('انستقرام')
        ->and($resource['name_en'])->toBe('Instagram')
        ->and($resource['name'])->toBe('Instagram')
        ->and($resource['link'])->toBe($social->link);
});

it('returns a null icon in the api resource when none is uploaded', function (): void {
    $social = Social::factory()->create(['icon' => null]);

    $resource = (new SocialResource($social))->toArray(request());

    expect($resource['icon'])->toBeNull()
        ->and($resource['name'])->toBe($social->name_en);
});

it('serves socials through the app api', function (): void {
    $social = Social::factory()->create(['name_ar' => 'انستقرام', 'icon' => 'socials/api.png']);

    $this->getJson(url('/api/socials'))
        ->assertOk()
        ->assertJsonPath('data.socials.0.name_ar', 'انستقرام')
        ->assertJsonPath('data.socials.0.icon', $social->icon_url);
});
