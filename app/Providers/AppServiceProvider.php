<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading();
        Paginator::useBootstrapFive();

        RateLimiter::for('sinkron', function ($request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?? $request->ip())
                ->response(function () {
                    return back()->with('error', 'Terlalu banyak request. Tunggu 1 menit.');
                });
        });
    }
}