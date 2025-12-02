<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class EditArticle
{
    /** @param array{} $args
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): Article|\Illuminate\Database\Eloquent\Collection
    {
        $validator = Validator::make($args, [
            'id' => ['required', 'integer', 'exists:articles,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'categoryIds' => ['nullable', 'array'],
            'categoryIds.*' => ['exists:categories,id']

        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $article = Article::findOrFail($args['id']);

        $updateData = collect($args)->except('id')->all();

        if (!empty($updateData)) {
            $article->fill($updateData);
            $article->save();
        }

        if (isset($args['categoryIds'])) {
            $article->categories()->attach($args['categoryIds']);
        }

        return $article;
    }
}
