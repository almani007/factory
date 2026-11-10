<?php

namespace App\Providers;

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\Company;
use App\Modules\Tenancy\Models\Factory;
use App\Modules\Products\Models\Item;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Accounting\Models\JournalEntry;
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

        // Register module service providers dynamically
        $this->registerModuleProviders();
    }

    protected function registerModuleProviders(): void
    {
        $modules = [
            \App\Modules\Core\Providers\CoreServiceProvider::class,
            \App\Modules\Tenancy\Providers\TenancyServiceProvider::class,
            \App\Modules\Identity\Providers\IdentityServiceProvider::class,
            \App\Modules\Organisation\Providers\OrganisationServiceProvider::class,
            \App\Modules\Configuration\Providers\ConfigurationServiceProvider::class,
            \App\Modules\Products\Providers\ProductsServiceProvider::class,
            \App\Modules\Inventory\Providers\InventoryServiceProvider::class,
            \App\Modules\Purchasing\Providers\PurchasingServiceProvider::class,
            \App\Modules\Manufacturing\Providers\ManufacturingServiceProvider::class,
            \App\Modules\Production\Providers\ProductionServiceProvider::class,
            \App\Modules\Quality\Providers\QualityServiceProvider::class,
            \App\Modules\Costing\Providers\CostingServiceProvider::class,
            \App\Modules\Sales\Providers\SalesServiceProvider::class,
            \App\Modules\Dispatch\Providers\DispatchServiceProvider::class,
            \App\Modules\Accounting\Providers\AccountingServiceProvider::class,
            \App\Modules\Maintenance\Providers\MaintenanceServiceProvider::class,
            \App\Modules\HR\Providers\HRServiceProvider::class,
            \App\Modules\Reporting\Providers\ReportingServiceProvider::class,
            \App\Modules\Notifications\Providers\NotificationsServiceProvider::class,
            \App\Modules\Integrations\Providers\IntegrationsServiceProvider::class,
        ];

        foreach ($modules as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}