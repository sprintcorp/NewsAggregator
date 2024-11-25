<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\NewsService;

class NewsAggregatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private NewsService $newsService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('Fetching articles...');

        // Fetch from The Guardian
        $guardianArticles = $this->newsService->fetchGuardianArticles();
        info('Fetched ' . count($guardianArticles) . ' articles from The Guardian.');

        // Fetch from NYT
        $nytArticles = $this->newsService->fetchNYTArticles();
        info('Fetched ' . count($nytArticles) . ' articles from NYT.');

        // Store articles
        $this->newsService->storeArticles(array_merge($guardianArticles, $nytArticles));

        info('Articles successfully stored.');
    }
}
