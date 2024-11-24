<?php

namespace App\Http\Services;

// use App\Http\Services\Contracts\NewsSourceInterface;
use App\Models\Article;
use App\Http\Services\TheGuardianSourceService;
use App\Http\Services\NewYorkTimesSourceService;


class ArticleService
{
    private array $sources;

    /**
     * Register all news sources.
     */
    public function __construct()
    {
        $this->sources = [
            new TheGuardianSourceService(),
            new NewYorkTimesSourceService()
        ];
    }

    /**
     * Fetch and store articles from all sources.
     */
    public function fetchAndStoreArticles(): void
    {
        foreach ($this->sources as $source) {
            $articles = $source->fetchArticles();

            foreach ($articles as $article) {
                $this->storeArticle($article);
            }
        }
    }

    /**
     * Store an article in the database.
     */
    private function storeArticle(array $articleData): void
    {
        if (!empty($articleData['news_id'])) {
            Article::updateOrCreate(
                ['news_id' => $articleData['news_id']],
                $articleData
            );
        }
    }
}
