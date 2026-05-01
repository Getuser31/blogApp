<?php declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Access\AuthorizationException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class AuthorDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
        directive @author on FIELD_DEFINITION
        GRAPHQL;
    }

    /**
     * Wrap around the final field resolver.
     *
     * @param FieldValue $fieldValue
     */
    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(function (callable $resolver) {
            return function($root, array $args, GraphQLContext $context, ResolveInfo $info) use ($resolver) {
                /** @var User $user */
                $user = $context->user();
                $roleName = strtolower($user->role?->name ?? '');
                if ($roleName !== 'author' && $roleName !== 'admin') {
                    throw new AuthorizationException('This action is unauthorized.');
                }
                return $resolver($root, $args, $context, $info);
            };
        });
    }
}
