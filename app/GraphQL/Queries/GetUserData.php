<?php

namespace App\GraphQL\Queries;

use App\Models\User;

class GetUserData
{
    public function __invoke($rootValue, array $args): ?User
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();

        if (! $currentUser) {
            return null;
        }

        $targetUser = User::find($args['id']);

        if (! $targetUser) {
            return null;
        }

        // Allow if admin or the user themselves
        if ($currentUser->isAdmin() || $currentUser->id === $targetUser->id) {
            return $targetUser;
        }

        return null;
    }
}
