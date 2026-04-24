<?php

use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin role', 'level' => 100]);
    Roles::factory()->create(['id' => 2, 'name' => 'User', 'slug' => 'user', 'description' => 'User role', 'level' => 1]);
});

test('it can register a new user', function () {
    $response = $this->graphQL('
        mutation {
            addUser(
                username: "JohnDoe"
                email: "john@example.com"
                password: "password123"
                passwordRepeat: "password123"
            ) {
                id
                name
                email
            }
        }
    ');

    $response->assertJsonStructure([
        'data' => [
            'addUser' => [
                'id',
                'name',
                'email',
            ]
        ]
    ]);

    expect($response->json('data.addUser.name'))->toBe('JohnDoe')
        ->and($response->json('data.addUser.email'))->toBe('john@example.com');
});

test('it can login with valid credentials', function () {
    User::factory()->withRole()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->graphQL('
        mutation {
            login(
                email: "test@example.com"
                password: "password123"
            ) {
                token
                user {
                    id
                    name
                    email
                }
            }
        }
    ');

    $response->assertJsonStructure([
        'data' => [
            'login' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ]
            ]
        ]
    ]);

    expect($response->json('data.login.user.email'))->toBe('test@example.com');
});

test('it cannot login with invalid credentials', function () {
    $response = $this->graphQL('
        mutation {
            login(
                email: "wrong@example.com"
                password: "wrongpassword"
            ) {
                token
                user {
                    id
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('it cannot login with a disabled account', function () {
    User::factory()->withRole()->disabled()->create([
        'email' => 'disabled@example.com',
    ]);

    $response = $this->graphQL('
        mutation {
            login(
                email: "disabled@example.com"
                password: "password"
            ) {
                token
                user {
                    id
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('it requires email and password for login', function () {
    $response = $this->graphQL('
        mutation {
            login(email: "", password: "") {
                token
                user {
                    id
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});
