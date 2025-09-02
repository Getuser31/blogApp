<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    /**
     * @param $root
     * @param array $args
     * @return array|JsonResponse
     * @throws ValidationException
     */
    public function login($root, array $args): array|JsonResponse
    {
        // Basic input validation
        $validator = Validator::make($args, [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $email = $args['email'];
        $password = $args['password'];

        // Find the user by email
        $user = User::where('email', $email)->first();

        // Validate credentials
        if (!$user || !Hash::check($password, $user->password)) {
            // Avoid leaking which field was incorrect
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        // Create Sanctum token
        $token = $user->createToken('graphql')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];

    }
}
