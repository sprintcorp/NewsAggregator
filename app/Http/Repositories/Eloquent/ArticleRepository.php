<?php

namespace App\Http\Repositories\Eloquent;

use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Preference;
use Illuminate\Support\Facades\Log;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getAll(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Article::query();

        if (!empty($filters['keyword'])) {
            $query->where('title', 'LIKE', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->whereRaw('LOWER(category) = ?', [strtolower($filters['category'])]);
        }

        if (!empty($filters['source'])) {
            $query->whereRaw('LOWER(source) = ?', [strtolower($filters['source'])]);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('published_date', $filters['date']);
        }

        return $query->paginate($perPage)->appends($filters);
    }


    public function findById(int $id): ?Article
    {
        return Article::find($id);
    }

    public function getArticlesByPreferences(Preference $preferences, int $perPage)
    {
        return Article::query()
        ->when(!empty($preferences->category), function ($query) use ($preferences) {
            $query->whereIn('category', $preferences->category);
        })
        ->when(!empty($preferences->author), function ($query) use ($preferences) {
            $query->whereIn('author', $preferences->author);
        })
        ->when(!empty($preferences->source), function ($query) use ($preferences) {
            $query->whereIn('source', $preferences->source);
        })
        ->orderBy('category')
        ->paginate($perPage);
    }
}
