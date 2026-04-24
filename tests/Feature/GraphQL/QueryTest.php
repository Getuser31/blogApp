<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin role', 'level' => 100]);
    Roles::factory()->create(['id' => 2, 'name' => 'User', 'slug' => 'user', 'description' => 'User role', 'level' => 1]);
});

test('it can fetch published articles', function () {
    Article::factory()->count(3)->published()->create();
    Article::factory()->create(['published' => false]);

    $response = $this->graphQL('
        {
            publishedArticles(first: 10) {
                data {
                    id
                    title
                    published
                }
            }
        }
    ');

    $articles = $response->json('data.publishedArticles.data');

    expect($articles)->toHaveCount(3);
    foreach ($articles as $article) {
        expect($article['published'])->toBeTrue();
    }
});

test('it can fetch articles by user', function () {
    $user = User::factory()->withRole()->create();
    Article::factory()->count(2)->create(['author_id' => $user->id]);
    Article::factory()->create(); // another user's article

    $response = $this->graphQL('
        {
            articlesByUser(userID: '.$user->id.', first: 10) {
                data {
                    id
                    title
                }
            }
        }
    ');

    expect($response->json('data.articlesByUser.data'))->toHaveCount(2);
});

test('it can fetch a single article by id', function () {
    $article = Article::factory()->published()->create();

    $response = $this->graphQL('
        {
            article(id: '.$article->id.') {
                id
                title
                content
                published
                author {
                    id
                    name
                }
            }
        }
    ');

    expect($response->json('data.article.title'))->toBe($article->title)
        ->and($response->json('data.article.id'))->toBe((string) $article->id);
});

test('it can search articles', function () {
    Article::factory()->create(['title' => 'Laravel Testing', 'published' => true]);
    Article::factory()->create(['title' => 'PHP Development', 'published' => true]);
    Article::factory()->create(['title' => 'JavaScript Guide', 'published' => true]);

    $response = $this->graphQL('
        {
            searchArticles(search: "Laravel", first: 10) {
                data {
                    id
                    title
                }
            }
        }
    ');

    expect($response->json('data.searchArticles.data'))->toHaveCount(1)
        ->and($response->json('data.searchArticles.data.0.title'))->toBe('Laravel Testing');
});

test('it can fetch all categories', function () {
    Category::factory()->count(3)->create();

    $response = $this->graphQL('
        {
            getCategories {
                id
                name
                slug
            }
        }
    ');

    expect($response->json('data.getCategories'))->toHaveCount(3);
});

test('it can fetch user by name', function () {
    User::factory()->withRole()->create(['name' => 'UniqueUser']);

    $response = $this->graphQL('
        {
            userByName(name: "UniqueUser") {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.userByName.name'))->toBe('UniqueUser');
});

test('it can fetch all users', function () {
    User::factory()->count(5)->withRole()->create();

    $response = $this->graphQL('
        {
            users {
                id
                name
                email
            }
        }
    ');

    expect($response->json('data.users'))->toHaveCount(5);
});
