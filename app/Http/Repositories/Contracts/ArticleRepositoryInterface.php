<?php

namespace App\Http\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Article;

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
}
