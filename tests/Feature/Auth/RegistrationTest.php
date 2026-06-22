<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register via email request flow', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'customer_limit' => 200,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Pendaftaran berhasil dikirim! Akun Anda sedang ditinjau oleh Master Admin. Silakan tunggu konfirmasi selanjutnya.');
});
