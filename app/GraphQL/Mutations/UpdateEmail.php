<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;

final readonly class UpdateEmail
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'email' => ['required', 'email', 'unique:users,email']
        ]);

        $validator->validate();

        if ($validator->fails()) {
            return new Error('Validation failed: ' . json_encode($validator->errors()));
        }

        $user = auth()->user();
        $user->email = $args['email'];
        $user->save();

        return $user;
    }
}
