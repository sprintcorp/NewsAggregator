<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Http;

class NewYorkTimesSourceService implements NewsSourceInterface
{
    private string $url = 'https://api.nytimes.com/svc/topstories/v2/arts.json';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('NY_TIMES_KEY');
    }

    /**
     * Fetch articles from The New York Times.
     */
    public function fetchArticles(): array
    {
        info('Fetch articles from The New York Times');
        $articles = [];
        $page = 0;

        do {
            $response = Http::get($this->url, [
                'api-key' => $this->apiKey,
                // 'page' => $page,
            ]);
            info($response);

            if ($response->failed()) {
                break;
            }

            $data = $response->json('results', []);
            if (empty($data)) {
                break;
            }

            foreach ($data as $item) {
                $articles[] = [
                    'news_id' => $item['uri'] ?? null,
                    'title' => $item['title'] ?? null,
                    'author' => $item['byline'] ?? null,
                    'source' => 'New York Times',
                    'category' => $item['section'] ?? null,
                    'published_date' => $item['published_date'] ?? null,
                    'body' => $item['abstract'] ?? null,
                ];
            }

            $page++;
        } while ($page <= 10);

        return $articles;
    }
}
