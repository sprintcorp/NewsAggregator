<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TheGuardianSourceService implements NewsSourceInterface
{
    private string $url = 'https://content.guardianapis.com/search';
    private string $apiKey;
    private Client $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->apiKey = config('services.guardian.api_key');
    }

    public function fetchArticles(): array
    {
        $articles = [];
        $pages = 5;

        for ($page = 1; $page <= $pages; $page++) {
            try {
                $response = $this->httpClient->get($this->url, [
                    'query' => [
                        'api-key' => $this->apiKey,
                        'page' => $page,
                        'show-fields' => 'byline,body',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $articles = array_merge($articles, $this->transformArticles($data));
            } catch (RequestException $e) {
                Log::error("Guardian API Request failed: {$e->getMessage()}");
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
