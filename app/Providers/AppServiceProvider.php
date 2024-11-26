<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Http\Repositories\Eloquent\ArticleRepository;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Repositories\Eloquent\UserRepository;
use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Http\Repositories\Eloquent\PreferenceRepository;
use App\Http\Services\ApiRequestService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(PreferenceRepositoryInterface::class, PreferenceRepository::class);
        $this->app->singleton(ApiRequestService::class, function ($app) {
            return new ApiRequestService(new \GuzzleHttp\Client());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-requests', function () {
            return Limit::perMinute(10)->response(function () {
                Log::warning('Rate limit exceeded for API requests.');
                return response('Rate limit exceeded. Please try again later.', 429);
            });
        });
    }
}
