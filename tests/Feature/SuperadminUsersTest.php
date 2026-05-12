<?php

use App\Models\User;
use App\Support\PasswordGenerator;
use Illuminate\Support\Facades\Hash;

test('a non-superadmin cannot access the superadmin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/superadmin')
        ->assertForbidden();
});

test('a superadmin can access the superadmin panel', function () {
    $admin = User::factory()->superadmin()->create();

    $this->actingAs($admin)
        ->get('/superadmin')
        ->assertOk();
});

test('an inactive user cannot access the admin panel', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('password generator returns mixed-class 16-char passwords', function () {
    $password = PasswordGenerator::generate();

    expect(strlen($password))->toBe(16)
        ->and($password)->toMatch('/[a-z]/')
        ->and($password)->toMatch('/[A-Z]/')
        ->and($password)->toMatch('/\d/')
        ->and($password)->toMatch('/[!@#$%^&*\-_=+?]/');
});

test('password generator can produce custom lengths', function () {
    expect(strlen(PasswordGenerator::generate(24)))->toBe(24);
});

test('a user created with a plaintext password gets it hashed', function () {
    $user = User::create([
        'name' => 'Test',
        'email' => 'test@playtask.test',
        'password' => 'plaintext-secret',
        'is_active' => true,
        'is_superadmin' => false,
        'email_verified_at' => now(),
    ]);

    expect(Hash::check('plaintext-secret', $user->password))->toBeTrue();
});
