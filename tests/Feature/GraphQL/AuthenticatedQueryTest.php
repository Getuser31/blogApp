<?php

use App\Models\Article;
use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin role', 'level' => 100]);
    Roles::factory()->create(['id' => 2, 'name' => 'User', 'slug' => 'user', 'description' => 'User role', 'level' => 1]);
});

test('authenticated user can fetch their own data', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        {
            me {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.me.email'))->toBe($user->email);
});

test('unauthenticated user cannot fetch me', function () {
    $response = $this->graphQL('
        {
            me {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.me'))->toBeNull();
});

test('authenticated user can fetch their articles', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    Article::factory()->count(3)->create(['author_id' => $user->id]);

    $response = $this->graphQL('
        {
            userArticles {
                id
                articles {
                    id
                    title
                }
            }
        }
    ');

    expect($response->json('data.userArticles.articles'))->toHaveCount(3);
});

test('authenticated user can fetch their favorite articles', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $articles = Article::factory()->count(2)->create();
    $user->favoriteArticles()->attach($articles->pluck('id'));

    $response = $this->graphQL('
        {
            getFavoriteArticles {
                id
                favoriteArticles {
                    id
                    title
                }
            }
        }
    ');

    expect($response->json('data.getFavoriteArticles.favoriteArticles'))->toHaveCount(2);
});

test('authenticated user can fetch another user by id', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $otherUser = User::factory()->withRole()->create();

    $response = $this->graphQL('
        {
            user(id: '.$otherUser->id.') {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.user.email'))->toBe($otherUser->email);
});

test('admin user can fetch all roles', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->graphQL('
        {
            getRoles {
                id
                name
                slug
            }
        }
    ');

    expect($response->json('data.getRoles'))->toHaveCount(2);
});

test('non-admin user cannot fetch roles', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        {
            getRoles {
                id
                name
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('unauthenticated user cannot retrieve user data via getUserData', function () {
    $response = $this->graphQL('
        {
            getUserData(id: 1) {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.getUserData'))->toBeNull();
});

test('authenticated user can retrieve their own data via getUserData', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        {
            getUserData(id: '.$user->id.') {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.getUserData.email'))->toBe($user->email);
});

test('authenticated user cannot retrieve another users data via getUserData', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $otherUser = User::factory()->withRole()->create();

    $response = $this->graphQL('
        {
            getUserData(id: '.$otherUser->id.') {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.getUserData'))->toBeNull();
});

test('admin can retrieve any users data via getUserData', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $otherUser = User::factory()->withRole()->create();

    $response = $this->graphQL('
        {
            getUserData(id: '.$otherUser->id.') {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.getUserData.email'))->toBe($otherUser->email);
});
