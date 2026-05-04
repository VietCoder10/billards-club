<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Event\EventInterface::class,
            \App\Repositories\Event\EventRepository::class
        );
        $this->app->bind(
            \App\Repositories\Customer\CustomerInterface::class,
            \App\Repositories\Customer\CustomerRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
