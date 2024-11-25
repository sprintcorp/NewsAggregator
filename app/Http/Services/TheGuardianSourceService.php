<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheGuardianSourceService implements NewsSourceInterface
{
    private string $url = 'https://content.guardianapis.com/search';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GUARDIAN_KEY');
    }

    /**
     * Fetch articles from The Guardian.
     *
     * @return array
     */
    public function fetchArticles(): array
    {
        $articles = [];
        $page = 1;
        $pageSize = 10;

        do {
            $response = Http::get($this->url, [
                'api-key' => $this->apiKey,
                'page-size' => $pageSize,  
                'page' => $page,
            ]);

            if ($response->failed()) {
                Log::error("Failed to fetch data from The Guardian API. Page: {$page}");
                break;
            }

            $responseData = $response->json('response');
            $results = $responseData['results'] ?? [];
            $totalPages = $responseData['pages'] ?? 1;

            if (empty($results)) {
                break;
            }

            foreach ($results as $item) {
                $articles[] = [
                    'news_id' => $item['id'] ?? null,
                    'title' => $item['webTitle'] ?? null,
                    'source' => 'The Guardian',
                    'category' => $item['sectionName'] ?? null,
                    'published_date' => $item['webPublicationDate'] ?? null,
                    'body' => $item['webUrl'] ?? null,
                ];
            }

            $page++;
        } while ($page <= $totalPages);

        return $articles;
    }
}
