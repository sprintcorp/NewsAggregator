<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;

class StoreArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private array $articles;

    /**
     * Create a new job instance.
     *
     * @param array $articles List of articles to store.
     */
    public function __construct(array $articles)
    {
        $this->articles = $articles;
    }

    public function handle(): void
    {
        foreach (array_chunk($this->articles, 50) as $chunk) {
            foreach ($chunk as $article) {
                Article::updateOrCreate(
                    ['news_id' => $article['news_id']],
                    $article
                );
            }
        }
    }
}
