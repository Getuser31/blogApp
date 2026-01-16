<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use Illuminate\Validation\ValidationException;

final readonly class AddFavoriteArticle
{
    /** @param array{} $args
     * @throws \Exception
     */
    public function __invoke(null $_, array $args)
    {
        $validator = \Validator::make($args, [
            'articleId' => ['required', 'integer', 'exists:articles,id']
        ]);

        $validator->validate();

        if (!$validator) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $article = Article::findOrFail($args['articleId']);

        $user = auth()->user();

        $user->favoriteArticles()->toggle($article);

        return $article;
    }
}
