<?php

test('public registration is disabled for the admin cms', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
    $this->assertGuest();
});

test('visitors cannot create accounts through registration', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
});
