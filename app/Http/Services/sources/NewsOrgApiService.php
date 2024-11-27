<?php

namespace App\Http\Services\sources;

use App\Http\Services\Contracts\NewsSourceInterface;
use App\Http\Services\ApiRequestService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NewsOrgApiService implements NewsSourceInterface
{
    private string $url = 'https://newsapi.org/v2/everything';
    private string $apiKey;
    private ApiRequestService $apiRequestService;

    public function __construct(ApiRequestService $apiRequestService)
    {
        $this->apiRequestService = $apiRequestService;
        $this->apiKey = config('services.newsapi.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 1; $page <= $pages; $page++) {
            $response = $this->apiRequestService->sendRequest(
                'newsorg-api',
                'GET',
                $this->url,
                [
                    'query' => [
                        'apiKey' => $this->apiKey,
                        'q' => 'latest',
                        'language' => 'en',
                        'pageSize' => 20,
                        'page' => $page,
                    ],
                ]
            );

            if (!empty($response)) {
                $articles = array_merge($articles, $this->transformArticles($response));
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
                    'source' => $item['source']['name'] ?? 'NewsAPI',
                    'author' => strip_tags($item['author'] ?? ''),
                    'category' => 'Latest',
                    'body' => strip_tags($item['description'] ?? ''),
                    'published_date' => Carbon::parse($item['publishedAt']),
                ];
            })->toArray();
    }
}
