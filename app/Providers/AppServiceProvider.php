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
        // Comutarea duratei de viață a serviciului injectat (Laborator 10 - Exercițiul 3):
        // - Transient (instanță nouă la fiecare rezolvare): $this->app->bind(ListingService::class);
        // - Scoped (o singură instanță per cerere HTTP / scope): $this->app->scoped(ListingService::class);
        // - Singleton (o singură instanță pe toată durata de viață a aplicației): $this->app->singleton(ListingService::class);
        $this->app->scoped(ListingService::class);
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
