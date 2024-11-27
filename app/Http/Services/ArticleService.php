<?php

namespace App\Http\Services;

use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleService
{
    protected ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function getFilteredArticles(array $filters, int $perPage = 10)
    {
        $page = request('page', 1);
        $cacheKey = $this->generateCacheKey($filters, $perPage, $page);

        return Cache::tags(['articles'])->remember(
            $cacheKey, now()->addMinutes(10), function () use ($filters, $perPage) {
            return $this->articleRepository->getAll($filters, $perPage);
        });
    }

    public function getArticleById(int $id)
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new ModelNotFoundException("Article not found.");
        }

        return $article;
    }

    private function generateCacheKey(array $filters, int $perPage, int $page): string
    {
        $filtersKey = md5(json_encode($filters));

        return "articles:filters:$filtersKey:per_page:$perPage:page:$page";
    }
}
