<?php

use App\Models\User;
use App\Models\BackboneDevice;

test('unauthenticated user is redirected to login', function () {
    $response = $this->get('/backbone-alerts');
    $response->assertRedirect('/login');
});

test('admin and noc roles can access backbone alerts page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $noc = User::factory()->create(['role' => 'noc', 'parent_admin_id' => $admin->id]);

    $this->actingAs($admin)->get('/backbone-alerts')->assertStatus(200);
    $this->actingAs($noc)->get('/backbone-alerts')->assertStatus(200);
});

test('unauthorized roles cannot access backbone alerts page', function () {
    $finance = User::factory()->create(['role' => 'finance']);
    $teknisi = User::factory()->create(['role' => 'teknisi']);

    $this->actingAs($finance)->get('/backbone-alerts')->assertStatus(403);
    $this->actingAs($teknisi)->get('/backbone-alerts')->assertStatus(403);
});

test('admin can create a backbone device', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $payload = [
        'name' => 'Core Switch A',
        'ip' => '10.0.0.1',
    ];

    $response = $this->actingAs($admin)
        ->postJson('/api/backbone-devices', $payload);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Core Switch A',
            'ip' => '10.0.0.1',
            'status' => 'up',
        ]);

    $this->assertDatabaseHas('backbone_devices', [
        'admin_id' => $admin->id,
        'name' => 'Core Switch A',
        'ip' => '10.0.0.1',
    ]);
});

test('admin can retrieve only their own backbone devices', function () {
    $admin1 = User::factory()->create(['role' => 'admin']);
    $admin2 = User::factory()->create(['role' => 'admin']);

    $device1 = BackboneDevice::create([
        'admin_id' => $admin1->id,
        'name' => 'Device Admin 1',
        'ip' => '192.168.1.1',
    ]);

    $device2 = BackboneDevice::create([
        'admin_id' => $admin2->id,
        'name' => 'Device Admin 2',
        'ip' => '192.168.2.1',
    ]);

    // Admin 1 sees only device 1
    $response1 = $this->actingAs($admin1)->getJson('/api/backbone-devices');
    $response1->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Device Admin 1'])
        ->assertJsonMissing(['name' => 'Device Admin 2']);

    // Admin 2 sees only device 2
    $response2 = $this->actingAs($admin2)->getJson('/api/backbone-devices');
    $response2->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Device Admin 2'])
        ->assertJsonMissing(['name' => 'Device Admin 1']);
});

test('admin can update a backbone device', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $device = BackboneDevice::create([
        'admin_id' => $admin->id,
        'name' => 'Old Name',
        'ip' => '192.168.1.1',
    ]);

    $payload = [
        'name' => 'New Name',
        'ip' => '192.168.1.100',
    ];

    $response = $this->actingAs($admin)
        ->putJson("/api/backbone-devices/{$device->id}", $payload);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'New Name',
            'ip' => '192.168.1.100',
        ]);

    $this->assertDatabaseHas('backbone_devices', [
        'id' => $device->id,
        'name' => 'New Name',
        'ip' => '192.168.1.100',
    ]);
});

test('admin can delete a backbone device', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $device = BackboneDevice::create([
        'admin_id' => $admin->id,
        'name' => 'Device to Delete',
        'ip' => '192.168.1.5',
    ]);

    $response = $this->actingAs($admin)
        ->deleteJson("/api/backbone-devices/{$device->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('backbone_devices', [
        'id' => $device->id,
    ]);
});

test('monitor:backbone command delays telegram alerts until failure exceeds 2 minutes', function () {
    \Illuminate\Support\Facades\Http::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'telegram_bot_token' => 'bot123456:ABC',
    ]);

    $teknisi = User::factory()->create([
        'role' => 'teknisi',
        'parent_admin_id' => $admin->id,
        'telegram_chat_id' => '6851158929',
    ]);

    // Create a device with status 'up'
    $device = BackboneDevice::create([
        'admin_id' => $admin->id,
        'name' => 'Failing Device',
        'ip' => '192.0.2.1', // TEST-NET-1 IP, ping will fail
        'status' => 'up',
    ]);

    // Run the Artisan command for the first failure transition
    $this->artisan('monitor:backbone');

    // Reload device from DB and assert status is down
    $device->refresh();
    expect($device->status)->toBe('down');
    expect($device->first_failed_at)->not->toBeNull();
    expect($device->telegram_alert_sent)->toBeFalse();

    // Assert NO Telegram alert was sent yet
    \Illuminate\Support\Facades\Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'sendMessage');
    });

    // Advance time by 125 seconds
    \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(125));

    // Run command again (still fails)
    $this->artisan('monitor:backbone');

    $device->refresh();
    expect($device->status)->toBe('down');
    expect($device->telegram_alert_sent)->toBeTrue();

    // Assert Telegram DOWN alert was sent
    \Illuminate\Support\Facades\Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.telegram.org/botbot123456:ABC/sendMessage') &&
               $request['chat_id'] === '6851158929' &&
               str_contains($request['text'], 'Failing Device') &&
               str_contains($request['text'], 'DOWN');
    });

    // Clear HTTP fake records to test UP alert transition
    \Illuminate\Support\Facades\Http::fake();

    // Update IP to local loopback (ping will succeed)
    $device->update(['ip' => '127.0.0.1']);

    // Run command again (should succeed)
    $this->artisan('monitor:backbone');

    $device->refresh();
    expect($device->status)->toBe('up');
    expect($device->first_failed_at)->toBeNull();
    expect($device->telegram_alert_sent)->toBeFalse();

    // Assert Telegram UP alert was sent
    \Illuminate\Support\Facades\Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.telegram.org/botbot123456:ABC/sendMessage') &&
               $request['chat_id'] === '6851158929' &&
               str_contains($request['text'], 'Failing Device') &&
               str_contains($request['text'], 'UP');
    });

    // Clean up test time
    \Illuminate\Support\Carbon::setTestNow(null);
});

test('monitor:backbone command does not send telegram alert if recovered within 2 minutes', function () {
    \Illuminate\Support\Facades\Http::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'telegram_bot_token' => 'bot123456:ABC',
    ]);

    $teknisi = User::factory()->create([
        'role' => 'teknisi',
        'parent_admin_id' => $admin->id,
        'telegram_chat_id' => '6851158929',
    ]);

    // Create a device with status 'up'
    $device = BackboneDevice::create([
        'admin_id' => $admin->id,
        'name' => 'Recovering Device',
        'ip' => '192.0.2.1', // TEST-NET-1 IP, ping will fail
        'status' => 'up',
    ]);

    // First check (fails)
    $this->artisan('monitor:backbone');

    $device->refresh();
    expect($device->status)->toBe('down');
    expect($device->telegram_alert_sent)->toBeFalse();

    // Advance time by 90 seconds (less than 2 minutes)
    \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(90));

    // Update IP to local loopback (ping will succeed)
    $device->update(['ip' => '127.0.0.1']);

    // Second check (succeeds)
    $this->artisan('monitor:backbone');

    $device->refresh();
    expect($device->status)->toBe('up');
    expect($device->first_failed_at)->toBeNull();
    expect($device->telegram_alert_sent)->toBeFalse();

    // Assert NO Telegram alert (neither DOWN nor UP) was ever sent
    \Illuminate\Support\Facades\Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'sendMessage');
    });

    // Clean up test time
    \Illuminate\Support\Carbon::setTestNow(null);
});

