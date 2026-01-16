<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class Login
{
    /**
     * Validates the provided arguments and authenticates a user by verifying their email and password.
     *
     * @param mixed $_ Unused parameter.
     * @param array $args Input data containing 'email' and 'password'.
     *
     * @return array|JsonResponse Upon successful authentication, returns an array containing the authentication token and user data.
     * @throws ValidationException If validation fails or authentication fails.
     *
     */
    public function __invoke($_, array $args): array|JsonResponse
    {
        $validator = Validator::make($args, [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $validator->validate();

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = User::where('email', $args['email'])->first();

        if (! $user || ! Hash::check($args['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (!$user->is_enabled) {
            throw ValidationException::withMessages([
                'email' => 'This account has been disabled.',
            ]);
        }

        return [
            'token' => $user->createToken('graphql')->plainTextToken,
            'user' => $user,
        ];
    }
}
