<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Repositories\User\UserInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\Building\BuildingInterface;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Invoice\InvoiceInterface;
use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\Report\ReportInterface;
use App\Repositories\Report\ReportRepository;
use Illuminate\Support\ServiceProvider;

class AdminProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (env('APP_ENV') != 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(InvoiceInterface::class, InvoiceRepository::class);
        $this->app->bind(ReportInterface::class, ReportRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
