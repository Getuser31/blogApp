<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Images;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Images>
 */
class ImagesFactory extends Factory
{
    protected $model = Images::class;

    public function definition(): array
    {
        return [
            'path' => fake()->imageUrl(),
            'article_id' => Article::factory(),
        ];
    }
}
