<?php

namespace App\Providers;

use App\Services\ListingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navigation', function ($view) {
            $currentPlan = null;
            if (auth()->check() && auth()->user()?->role !== 'admin') {
                $currentPlan = app(ListingService::class)->getUserCurrentPlan(auth()->id());
            }

            $view->with('currentUserPlan', $currentPlan);
        });
    }
}
