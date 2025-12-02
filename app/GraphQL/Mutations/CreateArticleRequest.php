<?php

namespace App\GraphQL\Mutations;

use Illuminate\Foundation\Http\FormRequest;

class CreateArticleRequest extends FormRequest
{
    /**
     * The GraphQL arguments for the request.
     *
     * @var array<string, mixed>
     */
    protected array $args = [];

    /**
     * Initializes the request with GraphQL arguments.
     */
    public function initialize(array $args = []): void
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by Lighthouse directives (@guard, @admin), so we can return true.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'categoryIds' => ['nullable', 'array'],
            'categoryIds.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get the data to be validated.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return $this->args;
    }
}
