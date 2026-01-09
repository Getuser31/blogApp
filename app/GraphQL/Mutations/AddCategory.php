<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class AddCategory
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'name' => ['required', 'string', 'max:255']
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $args['slug'] = Str::slug($args['name']);

        return Category::create($args);
    }
}
