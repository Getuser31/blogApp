<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use GraphQL\Error\Error;

final readonly class UpdatePassword
{
    /** @param array{} $args
     * @throws \Exception
     */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'oldPassword' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:25'],
            'passwordRepeat' => ['required', 'string', 'same:password']
        ]);

        if ($validator->fails()) {
            throw new Error('Validation failed: ' . json_encode($validator->errors()));
        }

        $user = Auth::user();

        if (!Hash::check($args['oldPassword'], $user->password)) {
            throw new Error('Old password is incorrect.');
        }

        $user->password = $args['password'];
        $user->save();

        return $user;
    }
}
