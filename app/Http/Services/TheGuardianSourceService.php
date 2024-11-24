<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Http;

class TheGuardianSourceService implements NewsSourceInterface
{
    private string $url = 'https://content.guardianapis.com/search';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GUARDIAN_KEY');
    }

    public function fetchArticles(): array
    {
        info('Fetch articles from The Guardian');
        $articles = [];
        $currentPage = 1;

        do {
            try {
                // Send the request for the current page
                $response = Http::retry(3, 100)->get($this->url, [
                    'api-key' => $this->apiKey,
                    'page-size' => 10, // Fixed page size
                    'page' => $currentPage,
                ]);

                if ($response->failed()) {
                    logger()->error("Failed response from The Guardian API", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                }

                // Parse the JSON response
                $data = $response->json('response');
                $results = $data['results'] ?? [];

                // Add the current page's articles to the array
                foreach ($results as $item) {
                    $articles[] = [
                        'news_id' => $item['id'] ?? null,
                        'title' => $item['webTitle'] ?? null,
                        'source' => 'The Guardian',
                        'category' => $item['sectionName'] ?? 'general',
                        'published_date' => $item['webPublicationDate'] ?? null,
                        'body' => $item['webUrl'] ?? null,
                    ];
                }

                // Increment the page and check if there are more pages
                $currentPage++;
                $totalPages = $data['pages'] ?? 1;

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                logger()->error("Connection error with The Guardian API", ['exception' => $e]);
                break; // Stop further attempts if connection fails
            }
        } while ($currentPage <= $totalPages); // Continue until all pages are fetched

        return $articles;
    }
}
