<?php

use App\Models\User;

test('admin can access settings page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/settings');

    $response->assertStatus(200);
});

test('admin can save telegram bot token', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->post('/settings/telegram', [
            'telegram_bot_token' => 'my-custom-bot-token',
        ]);

    $response->assertRedirect('/settings?tab=telegram');
    $response->assertSessionHas('success');

    $admin->refresh();
    $this->assertSame('my-custom-bot-token', $admin->telegram_bot_token);
});

test('unauthorized roles cannot save telegram bot token', function () {
    $teknisi = User::factory()->create(['role' => 'teknisi']);

    $response = $this->actingAs($teknisi)
        ->post('/settings/telegram', [
            'telegram_bot_token' => 'illegal-token',
        ]);

    $response->assertStatus(403); // Forbidden by role check middleware
});

test('admin can setup webhook successfully', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'telegram_bot_token' => 'my-custom-bot-token',
    ]);

    // Mock Telegram setWebhook API call
    Http::fake([
        'https://api.telegram.org/botmy-custom-bot-token/setWebhook' => Http::response(['ok' => true, 'result' => true], 200),
    ]);

    $response = $this->actingAs($admin)
        ->post('/settings/telegram/webhook');

    $response->assertRedirect('/settings?tab=telegram');
    $response->assertSessionHas('success');
});

test('webhook receives message and replies successfully', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'telegram_bot_token' => 'my-custom-bot-token',
    ]);

    // Mock Telegram sendMessage API call
    Http::fake([
        'https://api.telegram.org/botmy-custom-bot-token/sendMessage' => Http::response(['ok' => true], 200),
    ]);

    $payload = [
        'update_id' => 12345,
        'message' => [
            'message_id' => 1,
            'from' => ['id' => 999999, 'first_name' => 'John'],
            'chat' => ['id' => 999999, 'type' => 'private'],
            'text' => '/start',
        ],
    ];

    $response = $this->postJson('/api/telegram/webhook/my-custom-bot-token', $payload);

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'sendMessage') &&
            $request['chat_id'] === 999999 &&
            str_contains($request['text'], 'Selamat datang') &&
            str_contains($request['reply_markup'], 'Dapatkan Chat ID');
    });
});

test('webhook handles get chat id button and replies with chat id', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'telegram_bot_token' => 'my-custom-bot-token',
    ]);

    Http::fake([
        'https://api.telegram.org/botmy-custom-bot-token/sendMessage' => Http::response(['ok' => true], 200),
    ]);

    $payload = [
        'update_id' => 12346,
        'message' => [
            'message_id' => 2,
            'from' => ['id' => 999999, 'first_name' => 'John'],
            'chat' => ['id' => 999999, 'type' => 'private'],
            'text' => '🆔 Dapatkan Chat ID',
        ],
    ];

    $response = $this->postJson('/api/telegram/webhook/my-custom-bot-token', $payload);

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'sendMessage') &&
            $request['chat_id'] === 999999 &&
            str_contains($request['text'], '999999'); // Chat ID should be in response text
    });
});
