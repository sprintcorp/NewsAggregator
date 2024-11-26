<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewsAggregatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private string $sourceServiceClass;

    /**
     * Create a new job instance.
     *
     * @param string $sourceServiceClass Fully qualified class name of the service.
     */
    public function __construct(string $sourceServiceClass)
    {
        $this->sourceServiceClass = $sourceServiceClass;
    }

    public function handle(): void
    {
        $service = app($this->sourceServiceClass);

        $articles = $service->fetchArticles();

        dispatch(new StoreArticlesJob($articles));
    }
}
