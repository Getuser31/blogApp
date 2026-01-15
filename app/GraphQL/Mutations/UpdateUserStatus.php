<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

final readonly class UpdateUserStatus
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validate = Validator::make($args, [
            'userId' => 'required|integer|exists:users,id',
        ]);
        if ($validate->fails()) {
            return new Error('Validation failed: ' . json_encode($validate->errors()));
        }
        $user = User::findOrFail($args['userId']);

        if ($user->isAdmin()) {
            return new Error('You cannot disable an admin user.');
        }

        $user->is_enabled = !$user->is_enabled;
        $user->save();
        return $user;
    }
}
