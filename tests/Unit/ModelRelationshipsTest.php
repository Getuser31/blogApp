<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Comments;
use App\Models\Images;
use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'slug' => 'admin']);
    Roles::factory()->create(['id' => 2, 'slug' => 'user']);
});

test('comment belongs to an article', function () {
    $article = Article::factory()->create();
    $comment = Comments::factory()->create(['article_id' => $article->id]);

    expect($comment->article)->toBeInstanceOf(Article::class)
        ->and($comment->article->id)->toBe($article->id);
});

test('comment belongs to a user', function () {
    $user = User::factory()->withRole()->create();
    $comment = Comments::factory()->create(['user_id' => $user->id]);

    expect($comment->user)->toBeInstanceOf(User::class)
        ->and($comment->user->id)->toBe($user->id);
});

test('image belongs to an article', function () {
    $article = Article::factory()->create();
    $image = Images::factory()->create(['article_id' => $article->id]);

    expect($image->article)->toBeInstanceOf(Article::class)
        ->and($image->article->id)->toBe($article->id);
});

test('category belongs to many articles', function () {
    $category = Category::factory()->create();
    $articles = Article::factory()->count(2)->create();

    $category->articles()->attach($articles->pluck('id'));

    expect($category->articles)->toHaveCount(2)
        ->and($category->articles->first())->toBeInstanceOf(Article::class);
});

test('category has fillable name and slug', function () {
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    expect($category->name)->toBe('Technology')
        ->and($category->slug)->toBe('technology');
});

test('image has fillable fields', function () {
    $article = Article::factory()->create();
    $image = Images::create([
        'path' => '/images/test.jpg',
        'article_id' => $article->id,
    ]);

    expect($image->path)->toBe('/images/test.jpg');
});

test('comment has fillable fields', function () {
    $user = User::factory()->withRole()->create();
    $article = Article::factory()->create();
    $comment = Comments::create([
        'content' => 'Great article!',
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);

    expect($comment->content)->toBe('Great article!');
});
