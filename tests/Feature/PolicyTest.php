<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('policy is returned in english by default', function () {
    Setting::create(['key' => 'policy_ar', 'value' => 'عربي']);
    Setting::create(['key' => 'policy_en', 'value' => 'English policy content.']);

    $this->getJson('/api/policy')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['success', 'message', 'data' => ['policy' => ['language', 'content']]])
        ->assertJsonPath('data.policy.language', 'en')
        ->assertJsonPath('data.policy.content', 'English policy content.');
});

test('policy follows the lang header', function () {
    Setting::create(['key' => 'policy_ar', 'value' => 'محتوى السياسة بالعربية.']);
    Setting::create(['key' => 'policy_en', 'value' => 'English policy content.']);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/policy')
        ->assertOk()
        ->assertJsonPath('data.policy.language', 'ar')
        ->assertJsonPath('data.policy.content', 'محتوى السياسة بالعربية.');
});

test('policy returns an error when no content is seeded', function () {
    $this->getJson('/api/policy')
        ->assertStatus(400)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The policy is not available yet.');
});
