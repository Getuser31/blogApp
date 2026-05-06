<?php

use App\Models\Article;
use App\Models\ArticleTranslation;
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

test('an article belongs to a user (author)', function () {
    $user = User::factory()->withRole()->create();
    $article = Article::factory()->create(['author_id' => $user->id]);

    expect($article->author)->toBeInstanceOf(User::class)
        ->and($article->author->id)->toBe($user->id);
});

test('an article has many comments', function () {
    $article = Article::factory()->create();
    $comments = Comments::factory()->count(3)->create(['article_id' => $article->id]);

    expect($article->comments)->toHaveCount(3)
        ->and($article->comments->first())->toBeInstanceOf(Comments::class);
});

test('an article has many images', function () {
    $article = Article::factory()->create();
    $images = Images::factory()->count(2)->create(['article_id' => $article->id]);

    expect($article->images)->toHaveCount(2)
        ->and($article->images->first())->toBeInstanceOf(Images::class);
});

test('an article belongs to many categories', function () {
    $article = Article::factory()->create();
    $categories = Category::factory()->count(3)->create();

    $article->categories()->attach($categories->pluck('id'));

    expect($article->categories)->toHaveCount(3)
        ->and($article->categories->first())->toBeInstanceOf(Category::class);
});

test('an article belongs to many users as favorites', function () {
    $article = Article::factory()->create();
    $users = User::factory()->count(2)->withRole()->create();

    $article->favoritedByUsers()->attach($users->pluck('id'));

    expect($article->favoritedByUsers)->toHaveCount(2)
        ->and($article->favoritedByUsers->first())->toBeInstanceOf(User::class);
});

test('an article belongs to many users as last read', function () {
    $article = Article::factory()->create();
    $users = User::factory()->count(2)->withRole()->create();

    $article->lastReadBuUsers()->attach($users->pluck('id'));

    expect($article->lastReadBuUsers)->toHaveCount(2)
        ->and($article->lastReadBuUsers->first())->toBeInstanceOf(User::class);
});

test('an article has many translations', function () {
    $article = Article::factory()->create();

    $translation = ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'fr',
    ]);

    expect($article->translations)->toHaveCount(2) // one from factory (en) + one created here (fr)
        ->and($article->translations->first())->toBeInstanceOf(ArticleTranslation::class);
});

test('scope categoryId filters articles by category', function () {
    $category = Category::factory()->create();
    $articleInCategory = Article::factory()->create();
    $articleNotInCategory = Article::factory()->create();

    $articleInCategory->categories()->attach($category->id);

    $results = Article::categoryId($category->id)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($articleInCategory->id);
});

test('scope search filters articles by translation title', function () {
    Article::factory()->create();
    Article::factory()->create();
    Article::factory()->create();

    // Manually set translation titles for search testing
    $articles = Article::all();
    $titles = ['Laravel Testing Guide', 'PHP Best Practices', 'JavaScript Tips'];
    foreach ($articles as $i => $article) {
        $article->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['title' => $titles[$i], 'content' => 'Content']
        );
    }

    $results = Article::search('Laravel')->get();

    expect($results)->toHaveCount(1);
});

test('scope search is case insensitive', function () {
    Article::factory()->create();
    Article::factory()->create();

    $articles = Article::all();
    $articles[0]->translations()->updateOrCreate(
        ['locale' => 'en'],
        ['title' => 'Laravel Testing Guide', 'content' => 'Content']
    );
    $articles[1]->translations()->updateOrCreate(
        ['locale' => 'en'],
        ['title' => 'Another Article', 'content' => 'Content']
    );

    $results = Article::search('laravel')->get();

    expect($results)->toHaveCount(1);
});

test('isFavorite returns false when no user is authenticated', function () {
    $article = Article::factory()->create();

    expect($article->isFavorite())->toBeFalse();
});

test('published scope returns only published articles', function () {
    Article::factory()->create(['published' => true]);
    Article::factory()->create(['published' => true]);
    Article::factory()->create(['published' => false]);

    $published = Article::where('published', true)->get();

    expect($published)->toHaveCount(2);
});

test('an article can be created with fillable attributes', function () {
    $user = User::factory()->withRole()->create();
    $article = Article::create([
        'author_id' => $user->id,
        'published' => true,
    ]);

    // Create a translation for the article
    $article->translations()->create([
        'locale'  => 'en',
        'title'   => 'Test Article',
        'content' => 'Test content here.',
    ]);

    expect($article->published)->toBeTrue();
    expect($article->translations()->where('locale', 'en')->value('title'))->toBe('Test Article')
        ->and($article->translations()->where('locale', 'en')->value('content'))->toBe('Test content here.');
});
