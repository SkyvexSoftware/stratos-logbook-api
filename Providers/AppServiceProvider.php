<?php

namespace Modules\StratosLogbook\Providers;

use App\Contracts\Modules\ServiceProvider;

/**
 * @package Modules\StratosLogbook
 */
class AppServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(): void
    {
        $this->registerConfig();
    }

    public function register()
    {
        //
    }

    public function registerLinks(): void
    {
        //
    }

    protected function registerConfig()
    {
        // No config file shipped — endpoint behaviour is fully data-driven from
        // phpVMS native tables. Keep the method as an extension point.
    }
}
