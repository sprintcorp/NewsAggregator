<?php

namespace App\Http\Services\contracts;

interface NewsSourceInterface
{
    /**
     * Fetch articles from the source.
     *
     * @return array
     */
    public function fetchArticles(): array;
}
