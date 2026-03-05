<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TableProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\Tables\TableInterface::class,
            \App\Repositories\Tables\TabelRepository::class
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
