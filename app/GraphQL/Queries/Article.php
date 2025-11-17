<?php

namespace App\GraphQL\Queries;

use App\Models\Article as ArticleModel;

final class Article
{
    /**
     * @param null $_
     * @param  array{}  $args
     */
    public function __invoke(null $_, array $args)
    {
        // Find the article by its ID.
        // This is the same logic as the @find directive.
        return ArticleModel::where(
            (new ArticleModel)->getRouteKeyName(),
            $args['id']
        )->sole();
    }
}
