<?php

namespace App\GraphQL\Queries;

use App\Models\Article;

class PublishedLastArticles
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $query = Article::query()
            ->where('published', true)
            ->orderBy('created_at', 'DESC');

        if (isset($args['category_id'])) {
            $query->categoryId($args['category_id']);
        }

        $articles = $query->limit(8)->get();

        $hasMore = $articles->count() > 7;

        return [
            'articles' => $articles->slice(0, 7),
            'hasMore' => $hasMore,
        ];
    }
}
