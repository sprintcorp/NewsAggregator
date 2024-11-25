<?php

namespace App\Http\Repositories\Eloquent;

use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Preference;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getAll(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Article::query();

        if (!empty($filters['keyword'])) {
            $query->where('title', 'LIKE', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('published_date', $filters['date']);
        }

        // Return paginated results
        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * Fetch articles based on preferences.
     *
     * @param Preference $preferences
     * @param int $perPage
     * @return mixed
     */
    public function getArticlesByPreferences(Preference $preferences, int $perPage)
    {
        $query = Article::query();

        // Filter by categories
        if (!empty($preferences->category)) {
            $query->whereIn('category', $preferences->category);
        }

        // Filter by authors
        if (!empty($preferences->author)) {
            $query->whereIn('author', $preferences->author);
        }

        // Filter by sources
        if (!empty($preferences->source)) {
            $query->whereIn('source', $preferences->source);
        }

        // Paginate the results
        return $query->paginate($perPage);
    }
}
