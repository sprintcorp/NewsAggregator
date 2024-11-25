<?php

namespace App\Http\Services;

use App\Http\Services\Contracts\NewsSourceInterface;
use Illuminate\Support\Facades\Http;

class NewYorkTimesSourceService implements NewsSourceInterface
{
    private string $url = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('NY_TIMES_KEY');
    }

    /**
     * Fetch all articles from The New York Times.
     */
    public function fetchArticles(): array
    {
        info('Fetching all articles from The New York Times');
        $articles = [];
        $currentPage = 0; // NYT Article Search API uses zero-based indexing
        $totalPages = 1;  // Initialize to enter the loop

        do {
            try {
                // Send the request for the current page
                $response = Http::retry(3, 100)->get($this->url, [
                    'api-key' => $this->apiKey,
                    'page' => $currentPage, // Zero-based pagination
                ]);

                if ($response->failed()) {
                    logger()->error("Failed response from The New York Times API", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    // Handle rate limit errors
                    $responseBody = $response->json();
                    if (isset($responseBody['fault']['faultstring']) &&
                        str_contains($responseBody['fault']['faultstring'], 'Rate limit quota violation')) {
                        $retryAfter = (int)$response->header('Retry-After', 60); // Default to 60 seconds if not provided
                        logger()->warning("Rate limit exceeded. Waiting for {$retryAfter} seconds before retrying.");
                        sleep($retryAfter);
                        continue;
                    }

                    break;
                }

                // Parse the JSON response
                $data = $response->json('response.docs', []);
                $meta = $response->json('response.meta', []);

                // Add the current page's articles to the array
                foreach ($data as $item) {
                    $articles[] = [
                        'news_id' => $item['uri'] ?? null,
                        'title' => $item['headline']['main'] ?? null,
                        'author' => $item['byline']['original'] ?? null,
                        'source' => 'New York Times',
                        'category' => $item['section_name'] ?? 'Uncategorized',
                        'published_date' => $item['pub_date'] ?? null,
                        'body' => $item['abstract'] ?? null,
                    ];
                }

                // Determine total pages from meta information
                $totalPages = ceil(($meta['hits'] ?? 0) / 10); // NYT defaults to 10 results per page

                $currentPage++;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                logger()->error("Connection error with The New York Times API", ['exception' => $e]);
                break; // Stop further attempts if connection fails
            }
        } while ($currentPage < $totalPages); // Continue until all pages are fetched

        return $articles;
    }
}
