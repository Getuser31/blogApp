<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Images;
use App\Models\Roles;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Roles::factory()->create(['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin role', 'level' => 100]);
    Roles::factory()->create(['id' => 2, 'name' => 'User', 'slug' => 'user', 'description' => 'User role', 'level' => 1]);
});

test('it can create an article as admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();

    $response = $this->graphQL('
        mutation {
            createArticle(
                title: "Test Article"
                content: "This is the content of the test article."
                categoryIds: ['.$category->id.']
                publish: true
            ) {
                id
                title
                content
                published
                categories {
                    id
                    name
                }
            }
        }
    ');

    $response->assertJsonStructure([
        'data' => [
            'createArticle' => [
                'id',
                'title',
                'content',
                'published',
                'categories',
            ]
        ]
    ]);

    expect($response->json('data.createArticle.title'))->toBe('Test Article')
        ->and($response->json('data.createArticle.published'))->toBeTrue();
});

test('it cannot create an article without admin role', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        mutation {
            createArticle(
                title: "Test Article"
                content: "Content"
            ) {
                id
                title
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('it requires authentication to create an article', function () {
    $response = $this->graphQL('
        mutation {
            createArticle(
                title: "Test Article"
                content: "Content"
            ) {
                id
                title
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('it can edit an article as the author', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['author_id' => $user->id]);

    $response = $this->graphQL('
        mutation {
            editArticle(
                id: '.$article->id.'
                title: "Updated Title"
                content: "Updated content"
            ) {
                id
                title
                content
            }
        }
    ');

    expect($response->json('data.editArticle.title'))->toBe('Updated Title')
        ->and($response->json('data.editArticle.content'))->toBe('Updated content');
});

test('it can add a comment to an article', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $article = Article::factory()->create();

    $response = $this->graphQL('
        mutation {
            addComment(
                articleId: '.$article->id.'
                content: "Great article!"
            ) {
                id
                content
                user {
                    id
                    name
                }
            }
        }
    ');

    expect($response->json('data.addComment.content'))->toBe('Great article!')
        ->and($response->json('data.addComment.user.id'))->toBe((string) $user->id);
});

test('it requires authentication to add a comment', function () {
    $response = $this->graphQL('
        mutation {
            addComment(
                articleId: 1
                content: "Nice!"
            ) {
                id
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('it can add an article to favorites', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $article = Article::factory()->create();

    $response = $this->graphQL('
        mutation {
            addFavoriteArticle(
                articleId: '.$article->id.'
            ) {
                id
                title
            }
        }
    ');

    expect($response->json('data.addFavoriteArticle.id'))->toBe((string) $article->id);
    expect($user->fresh()->favoriteArticles)->toHaveCount(1);
});

test('it can toggle favorite on an article', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $article = Article::factory()->create();

    // Add to favorites
    $this->graphQL('
        mutation {
            addFavoriteArticle(articleId: '.$article->id.') { id }
        }
    ');

    // Remove from favorites (toggle)
    $this->graphQL('
        mutation {
            addFavoriteArticle(articleId: '.$article->id.') { id }
        }
    ');

    expect($user->fresh()->favoriteArticles)->toHaveCount(0);
});

test('it can add last read article', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $article = Article::factory()->create();

    $response = $this->graphQL('
        mutation {
            addLastReadArticle(
                articleId: '.$article->id.'
            ) {
                id
                title
            }
        }
    ');

    expect($response->json('data.addLastReadArticle.id'))->toBe((string) $article->id);
    expect($user->fresh()->lastReadArticles)->toHaveCount(1);
});

test('non-admin user cannot edit an article that does not belong to them', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $otherUser = User::factory()->withRole()->create();
    $article = Article::factory()->create(['author_id' => $otherUser->id]);

    $response = $this->graphQL('
        mutation {
            editArticle(
                id: '.$article->id.'
                title: "Hacked Title"
                content: "Hacked content"
            ) {
                id
                title
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('user cannot delete an image from an article that does not belong to them', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $otherUser = User::factory()->withRole()->create();
    $article = Article::factory()->create(['author_id' => $otherUser->id]);
    $image = Images::factory()->create(['article_id' => $article->id]);

    $response = $this->graphQL('
        mutation {
            deleteImage(id: '.$image->id.') {
                id
                path
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});
