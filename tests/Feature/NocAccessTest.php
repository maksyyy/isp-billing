<?php

use App\Models\User;

test('noc role can access dashboard successfully', function () {
    // 1. Create a NOC user
    $nocUser = User::factory()->create(['role' => 'noc']);

    // 2. Act as NOC and hit the dashboard route
    $response = $this->actingAs($nocUser)
        ->get('/dashboard');

    // 3. Assert 200 OK status
    $response->assertStatus(200);
});

test('noc role can view customers index', function () {
    // 1. Create a NOC user
    $nocUser = User::factory()->create(['role' => 'noc']);

    // 2. Act as NOC and hit the customers index route
    $response = $this->actingAs($nocUser)
        ->get('/customers');

    // 3. Assert 200 OK status
    $response->assertStatus(200);
});
