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
                translations {
                    locale
                    title
                    content
                }
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
                'translations',
                'published',
                'categories',
            ]
        ]
    ]);

    expect($response->json('data.createArticle.translations.0.title'))->toBe('Test Article')
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
                translations {
                    title
                }
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
                translations {
                    title
                }
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
                translations {
                    locale
                    title
                    content
                }
            }
        }
    ');

    expect($response->json('data.editArticle.translations.0.title'))->toBe('Updated Title')
        ->and($response->json('data.editArticle.translations.0.content'))->toBe('Updated content');
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
                translations {
                    locale
                    title
                }
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
                translations {
                    locale
                    title
                }
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
                translations {
                    title
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('admin can add a translation to their own article', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['author_id' => $user->id]);

    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: '.$article->id.'
                locale: "fr"
                title: "Titre en français"
                content: "Contenu en français"
            ) {
                id
                translations {
                    locale
                    title
                    content
                }
            }
        }
    ');

    $translations = $response->json('data.addArticleTranslation.translations');

    expect($translations)->toHaveCount(2);

    $frTranslation = collect($translations)->firstWhere('locale', 'fr');
    expect($frTranslation)->not->toBeNull()
        ->and($frTranslation['title'])->toBe('Titre en français')
        ->and($frTranslation['content'])->toBe('Contenu en français');
});

test('admin can add a translation to another user\'s article', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $otherUser = User::factory()->withRole()->create();
    $article = Article::factory()->create(['author_id' => $otherUser->id]);

    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: '.$article->id.'
                locale: "es"
                title: "Título en español"
                content: "Contenido en español"
            ) {
                id
                translations {
                    locale
                    title
                }
            }
        }
    ');

    $translations = $response->json('data.addArticleTranslation.translations');

    expect($translations)->toHaveCount(2);

    $esTranslation = collect($translations)->firstWhere('locale', 'es');
    expect($esTranslation['title'])->toBe('Título en español');
});

test('it can update an existing translation via addArticleTranslation', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['author_id' => $user->id]);

    // Original title is set by factory in English
    $originalTitle = $article->translations()->first()->title;

    // Update the existing "en" locale translation
    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: '.$article->id.'
                locale: "en"
                title: "Updated English Title"
                content: "Updated English content"
            ) {
                id
                translations {
                    locale
                    title
                    content
                }
            }
        }
    ');

    $translations = $response->json('data.addArticleTranslation.translations');

    // Should still have only 1 translation (updated, not created new)
    expect($translations)->toHaveCount(1);

    $enTranslation = collect($translations)->firstWhere('locale', 'en');
    expect($enTranslation['title'])->toBe('Updated English Title')
        ->and($enTranslation['content'])->toBe('Updated English content')
        ->and($enTranslation['title'])->not->toBe($originalTitle);
});

test('non-admin user cannot add a translation', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['author_id' => $user->id]);

    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: '.$article->id.'
                locale: "fr"
                title: "Titre"
                content: "Contenu"
            ) {
                id
                translations {
                    locale
                    title
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('unauthenticated user cannot add a translation', function () {
    $article = Article::factory()->create();

    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: '.$article->id.'
                locale: "fr"
                title: "Titre"
                content: "Contenu"
            ) {
                id
                translations {
                    locale
                    title
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('adding a translation with invalid articleId returns errors', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        mutation {
            addArticleTranslation(
                articleId: 99999
                locale: "fr"
                title: "Titre"
                content: "Contenu"
            ) {
                id
                translations {
                    locale
                    title
                }
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
