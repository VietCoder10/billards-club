<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\App\Providers\AdminProvider::class);
        $this->app->register(\App\Providers\Domain\AuthDomainProvider::class);
        $this->app->register(\App\Providers\SupplierProvider::class);
        $this->app->register(\App\Providers\ProductProvider::class);
        $this->app->register(\App\Providers\OptionProvider::class);
        $this->app->register(\App\Providers\TableProvider::class);
        $this->app->register(\App\Providers\OrderProvider::class);
        $this->app->register(\App\Providers\TablePriceMasterProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
