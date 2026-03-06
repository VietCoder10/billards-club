<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TablePriceMasterProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\TablePriceMaster\TablePriceMasterInterface::class,
            \App\Repositories\TablePriceMaster\TablePriceMasterRepository::class
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
