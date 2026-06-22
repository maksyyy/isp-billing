<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('master admin can create tenant admin without face photo but requires customer limit', function () {
    $master = User::factory()->create([
        'role' => 'master',
    ]);

    $response = $this->actingAs($master)->post('/users', [
        'name' => 'New Admin',
        'email' => 'newadmin@example.com',
        'phone' => '081234567890',
        'password' => 'password',
        'role' => 'admin',
        'customer_limit' => 500,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', [
        'email' => 'newadmin@example.com',
        'role' => 'admin',
        'customer_limit' => 500,
        'face_photo' => null,
    ]);
});

test('master dashboard displays tenant admins and handles search', function () {
    $master = User::factory()->create([
        'role' => 'master',
    ]);

    $admin1 = User::factory()->create([
        'name' => 'John Admin',
        'email' => 'john@example.com',
        'role' => 'admin',
    ]);

    $admin2 = User::factory()->create([
        'name' => 'Jane Admin',
        'email' => 'jane@example.com',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($master)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('John Admin');
    $response->assertSee('Jane Admin');

    // Search query
    $responseSearch = $this->actingAs($master)->get('/dashboard?search=Jane');
    $responseSearch->assertStatus(200);
    $responseSearch->assertSee('Jane Admin');
    $responseSearch->assertDontSee('John Admin');
});

test('tenant admin cannot add customers beyond their customer limit', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'customer_limit' => 2,
    ]);

    $package = Package::create([
        'admin_id' => $admin->id,
        'name'     => 'Gold Package',
        'price'    => 100000,
        'speed'    => '10 Mbps',
    ]);

    // Create 2 customers belonging to this admin
    Customer::create([
        'admin_id'      => $admin->id,
        'customer_code' => '0001',
        'name'          => 'Customer 1',
        'package_id'    => $package->id,
        'is_active'     => true,
    ]);

    Customer::create([
        'admin_id'      => $admin->id,
        'customer_code' => '0002',
        'name'          => 'Customer 2',
        'package_id'    => $package->id,
        'is_active'     => true,
    ]);

    // Attempting to add 3rd customer should fail
    $response = $this->actingAs($admin)->post('/customers', [
        'customer_code' => '0003',
        'name' => 'Customer 3',
        'package_id' => $package->id,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('customers', [
        'customer_code' => '0003',
    ]);
});
