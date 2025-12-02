<?php

namespace App\GraphQL\Traits;

trait ValidatesArticleCreation
{
    /**
     * Get the validation rules for creating an article.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'categoryIds' => ['nullable', 'array'],
            'categoryIds.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10120'],
        ];
    }
}
