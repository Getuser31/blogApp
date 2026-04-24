<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use Illuminate\Support\Facades\Validator;

final readonly class AddLastReadArticle
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validatedData = Validator::validate($args, [
            'articleId' => ['required', 'integer', 'exists:articles,id']
        ]);

        $article = Article::findOrFail($validatedData['articleId']);

        $user = auth()->user();

        $user->lastReadArticles()->sync([$article->id]);

        return $article;
    }
}
