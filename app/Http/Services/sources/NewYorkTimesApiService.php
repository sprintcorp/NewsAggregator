<?php

namespace App\Http\Services\Sources;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NewYorkTimesApiService implements NewsSourceInterface
{
    private string $url = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    private string $apiKey;
    private Client $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->apiKey = config('services.nyt.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 0; $page < $pages; $page++) {
            try {
                $response = $this->httpClient->get($this->url, [
                    'query' => [
                        'api-key' => $this->apiKey,
                        'page' => $page,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $articles = array_merge($articles, $this->transformArticles($data));
            } catch (RequestException $e) {
                Log::error("NYT API Request failed: {$e->getMessage()}");
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
