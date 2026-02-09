<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class AddUser
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validator = \Validator::make($args, [
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'passwordRepeat' => 'required|same:password',
        ]);

        $validator->validate();

        if($validator->failed()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return User::create([
            'name' => $args['username'],
            'email' => $args['email'],
            'password' => $args['password'],
            'role_id' => 2
        ]);
    }
}
