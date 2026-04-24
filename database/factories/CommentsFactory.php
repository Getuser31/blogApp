<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comments;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comments>
 */
class CommentsFactory extends Factory
{
    protected $model = Comments::class;

    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(),
            'user_id' => User::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
