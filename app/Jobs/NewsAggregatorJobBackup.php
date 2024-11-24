<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Article;

class NewsAggregatorJobBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sources = [
            [
                'url' => 'https://content.guardianapis.com/search',
                'source' => 'The Guardian',
                'apiKey' => env('GUARDIAN_KEY'),
                'params' => [
                    'api-key' => env('GUARDIAN_KEY'),
                    'page-size' => 10,
                ],
                'responseKey' => 'response.results',
            ],
            [
                'url' => 'https://api.nytimes.com/svc/search/v2/articlesearch.json',
                'source' => 'New York Times',
                'apiKey' => env('NY_TIMES_KEY'),
                'params' => [
                    'api-key' => env('NY_TIMES_KEY'),
                    'page' => 0,
                ],
                'responseKey' => 'response.docs',
            ]
        ];

        foreach ($sources as $source) {
            $page = 1;
            do {
                $params = $source['params'];
                if (isset($params['page'])) {
                    $params['page'] = $page - 1; // API pagination is zero-indexed
                } else {
                    $params['page'] = $page; // Default pagination
                }

                $response = Http::get($source['url'], $params);
                if ($response->failed()) {
                    break; // Exit loop on API failure
                }

                $data = data_get($response->json(), $source['responseKey'], []);

                if (empty($data)) {
                    break; // Exit if no data
                }

                foreach ($data as $item) {
                    $this->storeArticle($item, $source['source']);
                }

                $page++;
            } while ($page <= 10); // Limit pagination to avoid excessive API calls
        }
    }

    /**
     * Store an article in the database.
     *
     * @param array $item
     * @param string $source
     */
    protected function storeArticle(array $item, string $source): void
    {
        $articleData = $this->mapArticleData($item, $source);

        if (!empty($articleData['news_id'])) {
            Article::updateOrCreate(
                ['news_id' => $articleData['news_id']],
                $articleData
            );
        }
    }

    /**
     * Map article data to the database structure.
     *
     * @param array $item
     * @param string $source
     * @return array
     */
    protected function mapArticleData(array $item, string $source): array
    {
        if ($source === 'The Guardian') {
            return [
                'news_id' => $item['id'] ?? null,
                // 'author' => $item['byline'] ?? null,
                'title' => $item['webTitle'] ?? null,
                'source' => $source,
                'category' => $item['sectionName'] ?? 'general',
                'published_date' => $item['webPublicationDate'] ?? null,
                'body' => $item['webUrl'] ?? null,
            ];
        } elseif ($source === 'New York Times') {
            return [
                'news_id' => $item['uri'] ?? null,
                'author' => $item['byline'] ?? null,
                'title' => $item['headline']['main'] ?? null,
                'source' => $source,
                'category' => $item['section_name'] ?? 'general',
                'published_date' => $item['pub_date'] ?? null,
                'body' => $item['abstract'] ?? null,
            ];
        }

        return [];
    }
}
