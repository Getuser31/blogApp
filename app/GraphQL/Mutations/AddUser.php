<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class AddUser
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validation = \Validator::make($args, [
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'passwordRepeat' => 'required|same:password',
        ]);

        if($validation->failed()) {
            throw ValidationException::withMessages($validation->errors()->toArray());
        }

        $user = User::create([
            'name' => $args['username'],
            'email' => $args['email'],
            'password' => $args['password'],
            'role_id' => 2
        ]);

        return $user;
    }
}
