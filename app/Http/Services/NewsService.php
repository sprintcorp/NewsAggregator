<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use Illuminate\Support\Carbon;
use Mews\Purifier\Facades\Purifier;

class NewsService
{
    public function fetchGuardianArticles()
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => config('services.guardian.api_key'),
            'show-fields' => 'byline,body',
        ]);

        if ($response->successful()) {
            return $this->transformGuardianArticles($response->json());
        }

        return [];
    }

    public function fetchNYTArticles()
    {
        $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
            'api-key' => config('services.nyt.api_key'),
        ]);

        if ($response->successful()) {
            return $this->transformNYTArticles($response->json());
        }

        return [];
    }

    private function transformGuardianArticles(array $data)
    {
        return collect($data['response']['results'])->map(function ($item) {
            return $this->sanitizeArticle([
                'news_id' => $item['id'],
                'title' => $item['webTitle'],
                'source' => 'The Guardian',
                'author' => $item['fields']['byline'] ?? null,
                'category' => $item['sectionName'],
                'body' => $item['fields']['body'] ?? null,
                'published_date' => Carbon::parse($item['webPublicationDate']),
            ]);
        })->toArray();
    }

    private function transformNYTArticles(array $data)
    {
        return collect($data['response']['docs'])->map(function ($item) {
            return $this->sanitizeArticle([
                'news_id' => $item['_id'],
                'title' => $item['headline']['main'],
                'source' => 'The New York Times',
                'author' => $item['byline']['original'] ?? null,
                'category' => $item['section_name'] ?? null,
                'body' => $item['lead_paragraph'] ?? null,
                'published_date' => Carbon::parse($item['pub_date']),
            ]);
        })->toArray();
    }

    /**
     * Sanitize and clean article data.
     */
    private function sanitizeArticle(array $article)
    {
        return [
            'news_id' => $article['news_id'],
            'title' => strip_tags($article['title']), // Remove HTML from title
            'source' => strip_tags($article['source']),
            'author' => strip_tags($article['author']),
            'category' => strip_tags($article['category']),
            'body' => Purifier::clean($article['body']), // Remove unsafe HTML from body
            'published_date' => $article['published_date'],
        ];
    }

    public function storeArticles(array $articles)
    {
        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['news_id' => $article['news_id']],
                $article
            );
        }
    }
}
