<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class CreateArticle
{
    /**
     * Creates a new article.
     *
     * @param  null  $_
     * @param  array{title: string, content: string}  $args
     * @return \Illuminate\Database\Eloquent\Model
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): \Illuminate\Database\Eloquent\Model
    {
        $validator = Validator::make($args, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->articles()->create([
            'title' => $args['title'],
            'content' => $args['content'],
        ]);
    }
}
