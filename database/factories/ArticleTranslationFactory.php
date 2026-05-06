<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArticleTranslation>
 */
class ArticleTranslationFactory extends Factory
{
    protected $model = ArticleTranslation::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'locale'     => 'en',
            'title'      => fake()->sentence(),
            'content'    => fake()->paragraphs(3, true),
        ];
    }
}
