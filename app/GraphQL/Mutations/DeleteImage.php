<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Images;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class DeleteImage
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): Images
    {
        $validator = Validator::make($args, [
            'id' => ['required', 'integer', 'exists:images,id']
        ]);

        $validator->validate();

        if($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $image = Images::findOrFail($args['id']);
        $image->delete();
        return $image;
    }
}
