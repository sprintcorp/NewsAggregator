<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Repositories\Contracts\ArticleRepositoryInterface;
use App\Http\Repositories\Eloquent\ArticleRepository;
use App\Http\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Repositories\Eloquent\UserRepository;
use App\Http\Repositories\Contracts\PreferenceRepositoryInterface;
use App\Http\Repositories\Eloquent\PreferenceRepository;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
