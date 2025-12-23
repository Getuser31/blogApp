<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class AddComment
{
    /** @param array{} $args
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'articleId' => ['required', 'integer', 'exists:articles,id'],
            'content' => ['required', 'string'],
        ]);

        if($validator->failed()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $article = Article::findOrFail($args['articleId']);
        /** @var User $user */
        $user = Auth::user();

        return $article->comments()->create([
            'content' => $args['content'],
            'user_id' => $user->id
            ]);
    }
}
