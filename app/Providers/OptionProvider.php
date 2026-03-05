<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class OptionProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\Option\OptionInterface::class,
            \App\Repositories\Option\OptionRepository::class
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
