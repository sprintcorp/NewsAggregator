<?php

namespace App\Http\Transformers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleTransformer
{
    /**
     * Transform paginated articles for the list view (exclude the body field).
     */
    public function transformPaginated(LengthAwarePaginator $articles): array
    {
        return [
            'data' => array_map(function ($article) {
                return [
                    'id' => $article->id,
                    'news_id' => $article->news_id,
                    'title' => $article->title,
                    'source' => $article->source,
                    'author' => $article->author,
                    'category' => $article->category,
                    'published_date' => $article->published_date->toDateTimeString(),
                ];
            }, $articles->items()),
            'pagination' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'next_page_url' => $articles->nextPageUrl(),
                'prev_page_url' => $articles->previousPageUrl(),
            ],
        ];
    }

    /**
     * Transform a single article for the detail view (include the body field).
     */
    public function transformForDetail($article): array
    {
        return [
            'id' => $article->id,
            'news_id' => $article->news_id,
            'title' => $article->title,
            'source' => $article->source,
            'author' => $article->author,
            'category' => $article->category,
            'body' => $article->body,
            'published_date' => $article->published_date->toDateTimeString(),
        ];
    }
}
