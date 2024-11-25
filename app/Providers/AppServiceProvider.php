<?php

namespace App\Providers;

use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Repositories\Eloquent\ArticleRepository;
use App\Http\Repositories\Eloquent\UserRepository;
use App\Http\Services\Contracts\NewsSourceInterface;
use App\Http\Services\TheGuardianSourceService;
use App\Http\Services\NewYorkTimesSourceService;
use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Http\Repositories\Eloquent\PreferenceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(TheGuardianSourceService::class, NewsSourceInterface::class);
        $this->app->bind(NewYorkTimesSourceService::class, NewsSourceInterface::class);
        $this->app->bind(PreferenceRepositoryInterface::class, PreferenceRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
