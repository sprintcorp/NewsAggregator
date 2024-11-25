<?php

namespace App\Http\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Article;
use App\Models\Preference;

/**
 * Interface ArticleRepositoryInterface
 *
 * @package App\Http\Repositories\Contracts
 * @subpackage  ArticleRepository
 **/
interface ArticleRepositoryInterface
{
    public function getAll(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?Article;

    public function getArticlesByPreferences(Preference $preferences, int $perPage);
}
