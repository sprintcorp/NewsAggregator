<?php

namespace App\Http\Services\Contracts;

interface NewsSourceInterface
{
    /**
     * Fetch articles from the source.
     *
     * @return array
     */
    public function fetchArticles(): array;
}
