<?php

namespace App\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException; 

final readonly class CreateArticle
{
    /**
     * Creates a new article.
     *
     * @param  null  $_
     * @param  array{title: string, content: string, categoryIds?: array<int>}  $args
     * @return Model
     * @throws ValidationException
     */
    public function __invoke(null $_, array $args): Model
    {
        $validator = Validator::make($args, [
            'title' => ['required', 'string', 'max:255'], 
            'content' => ['required', 'string'], 
            'categoryIds' => ['nullable', 'array'], // categoryIds can be null or an array
            'categoryIds.*' => ['exists:categories,id'], // Each ID in the array must exist in the 'categories' table
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        /** @var \App\Models\User $user */ 
        $user = Auth::user();

        /** @var \App\Models\Article $article */
        $article = $user->articles()->create([
            'title' => $args['title'],
            'content' => $args['content'],
        ]);

        if (isset($args['categoryIds'])) {
            $article->categories()->attach($args['categoryIds']);
        }

        return $article;
    }
}
