<?php

namespace App\Http\Services\Sources;

use App\Http\Services\Contracts\NewsSourceInterface;
use App\Http\Services\ApiRequestService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class NewYorkTimesApiService implements NewsSourceInterface
{
    private string $url = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    private string $apiKey;
    private ApiRequestService $apiRequestService;

    public function __construct(ApiRequestService $apiRequestService)
    {
        $this->apiRequestService = $apiRequestService;
        $this->apiKey = config('services.nyt.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 0; $page < $pages; $page++) {
            $response = $this->apiRequestService->sendRequest(
                'nyt-api',
                'GET',
                $this->url,
                [
                    'query' => [
                        'api-key' => $this->apiKey,
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
        return collect($data['response']['docs'] ?? [])->map(function ($item) {
            return [
                'news_id' => $item['_id'],
                'title' => strip_tags($item['headline']['main']),
                'source' => 'The New York Times',
                'author' => strip_tags($item['byline']['original'] ?? ''),
                'category' => strip_tags($item['section_name'] ?? ''),
                'body' => strip_tags($item['lead_paragraph'] ?? ''),
                'published_date' => Carbon::parse($item['pub_date']),
            ];
        })->toArray();
    }
}
