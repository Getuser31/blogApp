<?php

namespace App\GraphQL\Traits;

use Illuminate\Contracts\Validation\ValidationRule;

trait ValidatesArticleCreation
{
    /**
     * Get the validation rules for creating an article.
     *
     * @return array<string, ValidationRule|array|string>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'categoryIds' => ['nullable', 'array'],
            'categoryIds.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image'],
            'publish' => ['nullable', 'boolean'],
        ];
    }
}
