<?php

namespace App\Http\Services\sources;

use App\Http\Services\Contracts\NewsSourceInterface;
use App\Http\Services\ApiRequestService;
use Illuminate\Support\Carbon;

class TheGuardianApiService implements NewsSourceInterface
{
    private string $url = 'https://content.guardianapis.com/search';
    private string $apiKey;
    private ApiRequestService $apiRequestService;

    public function __construct(ApiRequestService $apiRequestService)
    {
        $this->apiRequestService = $apiRequestService;
        $this->apiKey = config('services.guardian.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 1; $page <= $pages; $page++) {
            $response = $this->apiRequestService->sendRequest(
                'guardian-api', // Unique key for rate limiting
                'GET',
                $this->url,
                [
                    'query' => [
                        'api-key' => $this->apiKey,
                        'page' => $page,
                        'show-fields' => 'byline,body',
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
        return collect($data['response']['results'] ?? [])->map(function ($item) {
            return [
                'news_id' => $item['id'],
                'title' => strip_tags($item['webTitle']),
                'source' => 'The Guardian',
                'author' => strip_tags($item['fields']['byline'] ?? ''),
                'category' => strip_tags($item['sectionName'] ?? ''),
                'body' => strip_tags($item['fields']['body'] ?? ''),
                'published_date' => Carbon::parse($item['webPublicationDate']),
            ];
        })->toArray();
    }
}
