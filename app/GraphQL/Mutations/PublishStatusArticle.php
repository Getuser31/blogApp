<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;

final readonly class PublishStatusArticle
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validate = Validator::make($args, [
            'articleId' => ['required', 'exists:articles,id'],
            'publish' => ['required', 'boolean']
        ]);

        $validator->validate();

        if ($validate->fails()) {
            return new Error('Validation failed: ' . json_encode($validate->errors()));
        }

        $article = Article::findOrFail($args['articleId']);
        $article->published = $args['publish'];
        $article->save();

        return $article;
    }
}
