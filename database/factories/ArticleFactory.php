<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => true,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Article $article) {
            $article->translations()->create([
                'locale'  => 'en',
                'title'   => fake()->sentence(),
                'content' => fake()->paragraphs(3, true),
            ]);
        });
    }
}
