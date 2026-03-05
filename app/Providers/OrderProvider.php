<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class OrderProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\Order\OrderInterface::class,
            \App\Repositories\Order\OrderRepository::class
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
