<?php

namespace Modules\StratosLogbook\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Modules\StratosLogbook\Http\Controllers';

    public function before(Router $router) {}

    public function map(Router $router)
    {
        $this->registerApiRoutes();
    }

    protected function registerApiRoutes(): void
    {
        $config = [
            'as' => 'api.stratoslogbook.',
            'prefix' => 'api/stratos/logbook',
            'namespace' => $this->namespace.'\Api',
            'middleware' => ['api'],
        ];

        Route::group($config, function () {
            $this->loadRoutesFrom(__DIR__.'/../Http/Routes/api.php');
        });
    }
}
