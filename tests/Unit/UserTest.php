<?php

use App\Models\Article;
use App\Models\Comments;
use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'slug' => 'admin']);
    Roles::factory()->create(['id' => 2, 'slug' => 'user']);
});

test('a user has many articles', function () {
    $user = User::factory()->withRole()->create();
    Article::factory()->count(3)->create(['author_id' => $user->id]);

    expect($user->articles)->toHaveCount(3)
        ->and($user->articles->first())->toBeInstanceOf(Article::class);
});

test('a user has many comments', function () {
    $user = User::factory()->withRole()->create();
    $article = Article::factory()->create();
    Comments::factory()->count(2)->create([
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);

    expect($user->comments)->toHaveCount(2)
        ->and($user->comments->first())->toBeInstanceOf(Comments::class);
});

test('a user can favorite many articles', function () {
    $user = User::factory()->withRole()->create();
    $articles = Article::factory()->count(3)->create();

    $user->favoriteArticles()->attach($articles->pluck('id'));

    expect($user->favoriteArticles)->toHaveCount(3)
        ->and($user->favoriteArticles->first())->toBeInstanceOf(Article::class);
});

test('a user has last read articles', function () {
    $user = User::factory()->withRole()->create();
    $articles = Article::factory()->count(2)->create();

    $user->lastReadArticles()->attach($articles->pluck('id'));

    expect($user->lastReadArticles)->toHaveCount(2);
});

test('a user can check if admin', function () {
    $admin = User::factory()->admin()->create();
    $regularUser = User::factory()->withRole()->create();

    expect($admin->isAdmin())->toBeTrue()
        ->and($regularUser->isAdmin())->toBeFalse();
});

test('hasFavorited returns true when user has favorites', function () {
    $user = User::factory()->withRole()->create();
    $article = Article::factory()->create();

    $user->favoriteArticles()->attach($article->id);

    expect($user->hasFavorited())->toBeTrue();
});

test('hasFavorited returns false when user has no favorites', function () {
    $user = User::factory()->withRole()->create();

    expect($user->hasFavorited())->toBeFalse();
});

test('getFavoriteArticles returns collection of favorited articles', function () {
    $user = User::factory()->withRole()->create();
    $articles = Article::factory()->count(2)->create();

    $user->favoriteArticles()->attach($articles->pluck('id'));

    $favorites = $user->getFavoriteArticles();

    expect($favorites)->toHaveCount(2)
        ->and($favorites)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

test('user belongs to a role', function () {
    $user = User::factory()->withRole(2)->create();

    expect($user->role)->not->toBeNull()
        ->and($user->role->name)->toBeString();
});
