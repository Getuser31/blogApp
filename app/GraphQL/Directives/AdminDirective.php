<?php declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class AdminDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Limit field access to users with the admin role.
            """
        directive @admin on FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(function (callable $resolver) {
            return function ($root, array $args, GraphQLContext $context, ResolveInfo $info) use ($resolver) {
                /** @var User|null $user */
                $user = $context->user();

                if (! $user) {
                    throw new AuthenticationException('Unauthenticated.');
                }

                // We use the nullsafe operator (?->) in case the user has no role assigned.
                // We use strtolower to match "admin" regardless of casing.
                if (strtolower($user->role?->name ?? '') !== 'admin') {
                    throw new AuthorizationException('Unauthorized: You must be an admin.');
                }
                return $resolver($root, $args, $context, $info);
            };
        });
    }
}
