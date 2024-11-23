<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use App\Models\Article;

class NewsAggregatorJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('Executing');
        $sources = [
            [
                'url' => 'https://eventregistry.org/api/v1/article/getArticles',
                'source' => 'NewsApi',
                'apiKey' => env('NEWS_API_KEY')
            ]
        ];


        foreach ($sources as $source) {
            $response = Http::get($source['url'], [
                'apiKey' => $source['apiKey'],
                'action' => 'getArticles',
                'keyword' => 'Tesla Inc',
                'sourceLocationUri' => [
                    'http://en.wikipedia.org/wiki/United_States',
                    'http://en.wikipedia.org/wiki/Canada',
                    'http://en.wikipedia.org/wiki/United_Kingdom'
                ],
                'ignoreSourceGroupUri' => 'paywall/paywalled_sources',
                'articlesPage' => 1,
                'articlesCount' => 100,
                'articlesSortBy' => 'date',
                'articlesSortByAsc' => false,
                'dataType' => [
                    'news',
                    'pr'
                ],
                'forceMaxDataTimeWindow' => 31,
                'resultType' => 'articles',
            ]);


            $data = $response->json();
            // info($data['articles']['results']);

            foreach ($data['articles']['results'] as $item) {
                info($item['title']);
                Article::updateOrCreate([
                    'title' => $item['title'],
                ], [
                    'source' => $source['source'],
                    'category' => $item['category'] ?? 'general',
                    'published_date' => $item['dateTimePub'],
                    'content' => $item['body'],
                ]);
            }
        }
    }
}
