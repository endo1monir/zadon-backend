<?php

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('notification returns title and message in english by default', function () {
    $user = User::create(['name' => 'عميل', 'phone' => '0551111111', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    UserNotification::create([
        'user_id' => $user->id,
        'title_ar' => 'طلب جديد',
        'title_en' => 'New order',
        'message_ar' => 'تم إنشاء طلبك بنجاح.',
        'message_en' => 'Your order has been created successfully.',
    ]);

    $this->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.notifications.0.title', 'New order')
        ->assertJsonPath('data.notifications.0.message', 'Your order has been created successfully.');
});

test('notification title and message follow the lang header', function () {
    $user = User::create(['name' => 'عميل', 'phone' => '0552222222', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    UserNotification::create([
        'user_id' => $user->id,
        'title_ar' => 'طلب جديد',
        'title_en' => 'New order',
        'message_ar' => 'تم إنشاء طلبك بنجاح.',
        'message_en' => 'Your order has been created successfully.',
    ]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.notifications.0.title', 'طلب جديد')
        ->assertJsonPath('data.notifications.0.message', 'تم إنشاء طلبك بنجاح.');
});

test('listing notifications marks them as read', function () {
    $user = User::create(['name' => 'عميل', 'phone' => '0553333333', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    UserNotification::create([
        'user_id' => $user->id,
        'title_ar' => 'طلب جديد',
        'title_en' => 'New order',
    ]);

    $this->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.notifications.0.is_read', true);

    $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'is_read' => true]);
});
