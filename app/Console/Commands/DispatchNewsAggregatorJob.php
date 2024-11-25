<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\NewsAggregatorJob;
use App\Http\Services\NewYorkTimesSourceService;
use App\Http\Services\TheGuardianSourceService;

class DispatchNewsAggregatorJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispatch-news-aggregator-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the NewsAggregatorJob to fetch news articles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        NewsAggregatorJob::dispatch(NewYorkTimesSourceService::class);
        NewsAggregatorJob::dispatch(TheGuardianSourceService::class);

        $this->info('NewsAggregatorJobs dispatched for all sources.');
    }
}
