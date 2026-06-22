<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;

test('authenticated admin can bulk delete invoices', function () {
    // 1. Setup User with admin role
    $admin = User::factory()->create(['role' => 'admin']);

    // 2. Setup a package and customer (needed for invoice)
    $package = Package::create([
        'name' => 'Premium Package',
        'price' => 150000,
        'speed' => '10 Mbps'
    ]);

    $customer = Customer::create([
        'customer_code' => '1001',
        'name' => 'John Doe',
        'package_id' => $package->id,
        'status' => 'active',
        'whatsapp' => '08123456789'
    ]);

    // 3. Create two invoices
    $invoice1 = Invoice::create([
        'customer_id' => $customer->id,
        'amount' => 150000,
        'due_date' => now()->addDays(7),
        'status' => 'unpaid',
        'paid_amount' => 0
    ]);

    $invoice2 = Invoice::create([
        'customer_id' => $customer->id,
        'amount' => 150000,
        'due_date' => now()->addDays(7),
        'status' => 'unpaid',
        'paid_amount' => 0
    ]);

    $this->assertDatabaseHas('invoices', ['id' => $invoice1->id]);
    $this->assertDatabaseHas('invoices', ['id' => $invoice2->id]);

    // 4. Act as admin and post to delete-selected route
    $response = $this->actingAs($admin)
        ->post('/invoices/delete-selected', [
            'invoice_ids' => [$invoice1->id, $invoice2->id]
        ]);

    // 5. Assert redirect back with success flash message
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);

    // 6. Assert database no longer has the invoices
    $this->assertDatabaseMissing('invoices', ['id' => $invoice1->id]);
    $this->assertDatabaseMissing('invoices', ['id' => $invoice2->id]);
});

test('unauthorized roles cannot bulk delete invoices', function () {
    // 1. Setup User with non-admin/non-finance role (e.g. teknisi)
    $teknisi = User::factory()->create(['role' => 'teknisi']);

    // 2. Setup a package and customer
    $package = Package::create([
        'name' => 'Premium Package 2',
        'price' => 150000,
        'speed' => '10 Mbps'
    ]);

    $customer = Customer::create([
        'customer_code' => '1002',
        'name' => 'Jane Doe',
        'package_id' => $package->id,
        'status' => 'active',
        'whatsapp' => '08123456789'
    ]);

    // 3. Create an invoice
    $invoice = Invoice::create([
        'customer_id' => $customer->id,
        'amount' => 150000,
        'due_date' => now()->addDays(7),
        'status' => 'unpaid',
        'paid_amount' => 0
    ]);

    // 4. Act as teknisi and try to delete
    $response = $this->actingAs($teknisi)
        ->post('/invoices/delete-selected', [
            'invoice_ids' => [$invoice->id]
        ]);

    // 5. Assert Forbidden status (403) or redirect depending on how role middleware works
    // Since role middleware throws 403 or redirects, let's verify it didn't delete the invoice
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
});
