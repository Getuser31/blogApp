<?php declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
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
                if(strtolower($user->role?->name ?? '') !== 'author' && strtolower($user->role?->name ?? '') !== 'admin'){
                    return null;
                }
                return $resolver($root, $args, $context, $info);
            };
        });
    }
}
