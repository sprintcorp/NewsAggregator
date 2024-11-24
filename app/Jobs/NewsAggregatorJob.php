<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\ArticleService;

class NewsAggregatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private ArticleService $articleService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->articleService = new ArticleService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->articleService->fetchAndStoreArticles();
    }
}
