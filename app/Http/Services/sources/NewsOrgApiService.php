<?php

namespace App\Http\Services\Sources;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NewsOrgApiService implements NewsSourceInterface
{
    private string $url = 'https://newsapi.org/v2/everything';
    private string $apiKey;
    private Client $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->apiKey = config('services.newsapi.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 1; $page <= $pages; $page++) {
            try {
                $response = $this->httpClient->get($this->url, [
                    'query' => [
                        'apiKey' => $this->apiKey,
                        'q' => 'latest',
                        'language' => 'en',
                        'pageSize' => 20,
                        'page' => $page,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $articles = array_merge($articles, $this->transformArticles($data));
            } catch (RequestException $e) {
                Log::error("NewsAPI Request failed: {$e->getMessage()}");
            }
        }

        return $articles;
    }

    private function transformArticles(array $data): array
    {
        return collect($data['articles'] ?? [])
            ->reject(fn($item) => strip_tags($item['title']) === '[Removed]')
            ->map(function ($item) {
                return [
                    'news_id' => md5($item['url']),
                    'title' => strip_tags($item['title']),
                    'source' => 'NewsAPI',
                    'author' => strip_tags($item['author'] ?? ''),
                    'category' => 'Latest',
                    'body' => strip_tags($item['description'] ?? ''),
                    'published_date' => Carbon::parse($item['publishedAt']),
                ];
            })->toArray();
    }
}
