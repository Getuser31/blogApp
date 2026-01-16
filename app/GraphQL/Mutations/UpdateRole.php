<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Roles;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;

final readonly class UpdateRole
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {

        $validate = Validator::make($args, [
            'userId' => 'required|integer|exists:users,id',
            'roleId' => 'required|integer|exists:roles,id'
        ]);
        $validate->validate();

        if ($validate->fails()) {
            return new Error('Validation failed: ' . json_encode($validate->errors()));
        }
        $user = User::findOrFail($args['userId']);

        $role = Roles::findOrFail($args['roleId']);

        $user->role()->associate($role);
        $user->save();

        return $user;
    }
}
