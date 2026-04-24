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

test('admin can create a category', function () {
    $response = $this->graphQL('
        mutation {
            addCategory(name: "Technology") {
                id
                name
                slug
            }
        }
    ');

    expect($response->json('data.addCategory.name'))->toBe('Technology')
        ->and($response->json('data.addCategory.slug'))->toBe('technology');
});

test('admin can delete a category', function () {
    $category = Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);

    $response = $this->graphQL('
        mutation {
            deleteCategory(id: '.$category->id.') {
                id
                name
            }
        }
    ');

    expect($response->json('data.deleteCategory.id'))->toBe((string) $category->id);
    expect(Category::find($category->id))->toBeNull();
});

test('admin can delete an image', function () {
    $article = Article::factory()->create();
    $image = Images::factory()->create(['article_id' => $article->id]);

    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->graphQL('
        mutation {
            deleteImage(id: '.$image->id.') {
                id
                path
            }
        }
    ');

    expect($response->json('data.deleteImage.id'))->toBe((string) $image->id);
    expect(Images::find($image->id))->toBeNull();
});

test('admin can update user role', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $user = User::factory()->withRole()->create();

    $response = $this->graphQL('
        mutation {
            updateRole(userId: '.$user->id.', roleId: 1) {
                id
                role {
                    id
                    name
                }
            }
        }
    ');

    expect((int) $response->json('data.updateRole.role.id'))->toBe(1);
});

test('non-admin cannot update user role', function () {
    $user = User::factory()->withRole()->create();
    $this->actingAs($user);

    $otherUser = User::factory()->withRole()->create();

    $response = $this->graphQL('
        mutation {
            updateRole(userId: '.$otherUser->id.', roleId: 1) {
                id
                role {
                    id
                }
            }
        }
    ');

    expect($response->json('errors'))->not->toBeNull();
});

test('admin can toggle publish status of an article', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['published' => false]);

    $response = $this->graphQL('
        mutation {
            togglePublishStatus(articleId: '.$article->id.', publish: true) {
                id
                published
            }
        }
    ');

    expect($response->json('data.togglePublishStatus.published'))->toBeTrue();
});

test('admin can update user status', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $user = User::factory()->withRole()->create(['is_enabled' => true]);

    $response = $this->graphQL('
        mutation {
            updateUserStatus(userId: '.$user->id.') {
                id
                is_enabled
            }
        }
    ');

    expect($response->json('data.updateUserStatus.is_enabled'))->toBeFalse();
});
