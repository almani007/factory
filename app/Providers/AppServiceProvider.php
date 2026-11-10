<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

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
        Model::preventAccessingMissingAttributes();

        // Register module service providers dynamically (DDD src/ structure)
        $this->registerModuleProviders();
    }

    protected function registerModuleProviders(): void
    {
        $modules = [
            \Tenancy\Infrastructure\Provider\TenancyServiceProvider::class,
            \Identity\Infrastructure\Provider\IdentityServiceProvider::class,
            \Organisation\Infrastructure\Provider\OrganisationServiceProvider::class,
            \Configuration\Infrastructure\Provider\ConfigurationServiceProvider::class,
            \Products\Infrastructure\Provider\ProductsServiceProvider::class,
            \Inventory\Infrastructure\Provider\InventoryServiceProvider::class,
            \Purchasing\Infrastructure\Provider\PurchasingServiceProvider::class,
            \Manufacturing\Infrastructure\Provider\ManufacturingServiceProvider::class,
            \Production\Infrastructure\Provider\ProductionServiceProvider::class,
            \Quality\Infrastructure\Provider\QualityServiceProvider::class,
            \Costing\Infrastructure\Provider\CostingServiceProvider::class,
            \Sales\Infrastructure\Provider\SalesServiceProvider::class,
            \Dispatch\Infrastructure\Provider\DispatchServiceProvider::class,
            \Accounting\Infrastructure\Provider\AccountingServiceProvider::class,
            \Maintenance\Infrastructure\Provider\MaintenanceServiceProvider::class,
            \HR\Infrastructure\Provider\HRServiceProvider::class,
            \Reporting\Infrastructure\Provider\ReportingServiceProvider::class,
            \Notifications\Infrastructure\Provider\NotificationsServiceProvider::class,
            \Integrations\Infrastructure\Provider\IntegrationsServiceProvider::class,
        ];

        foreach ($modules as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}