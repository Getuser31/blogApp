<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class AddArticleTranslation
{
    /**
     * Add a translated version of an existing article.
     *
     * @param  null  $_
     * @param  array{articleId: int, locale: string, title: string, content: string}  $args
     * @return Article
     *
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): Article
    {
        $validator = Validator::make($args, [
            'articleId' => ['required', 'integer', 'exists:articles,id'],
            'locale'    => ['required', 'string', 'max:10'],
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string'],
        ]);

        $validator->validate();

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        $article = Article::findOrFail($validated['articleId']);

        $user = auth()->user();

        if ($article->author_id !== $user->getId() && !$user->isAdmin()) {
            throw new \Exception('You are not authorized to add translations to this article.');
        }

        $article->translations()->updateOrCreate(
            ['locale' => $validated['locale']],
            [
                'title'   => $validated['title'],
                'content' => $validated['content'],
            ]
        );

        return $article->fresh('translations');
    }
}
