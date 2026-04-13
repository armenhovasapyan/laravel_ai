<?php

use App\Models\User;


beforeEach(function () {
    // Clear rate limiters for test email to ensure clean state
    $throttleKey = Str::transliterate(Str::lower('test@example.com') . '|' . '127.0.0.1');
    RateLimiter::clear($throttleKey);
});

test('user can login with valid credentials via api', function() {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id', 'name', 'email'
            ],
            'token'
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ])
    ;

    expect($response->json('token'))->not->toBeEmpty();

    $this->assertAuthenticated();
});

test('user cannot login with invalid email via api', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});

test('user cannot login with invalid password via api', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    $this->assertGuest();
});


test('login requires email field', function () {
    $response = $this->postJson('/api/login', [
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login requires password field', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('login requires valid email format', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login is rate limited after too many attempts', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    // 6th attempt should be rate limited
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login works after rate limit expires', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    // Make 5 failed attempts to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    // Clear rate limiter using the same throttle key format as LoginRequest
    $throttleKey = Str::transliterate(Str::lower('test@example.com') . '|' . '127.0.0.1');
    RateLimiter::clear($throttleKey);

    // Now login should work
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email'
            ],
            'token'
        ]);

    $this->assertAuthenticated();
});

test('login endpoint requires guest middleware', function () {
    $user = User::factory()->create();

    // Authenticated user should not be able to access login
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

    // This should redirect or return 302/401 depending on middleware configuration
    $response->assertStatus(302);
});

test('login response contains correct user data structure', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ]);

    // Verify password is not exposed in response
    $responseData = $response->json();
    expect($responseData['user'])->not->toHaveKey('password');
    expect($responseData['user'])->not->toHaveKey('remember_token');
});
