<?php

namespace Modules\StratosLogbook\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // The Stratos auth middleware queries the `users` table to resolve the
        // Bearer token, so even an unauthenticated request needs the table to
        // exist. Create the minimum surface here — Testbench's in-memory sqlite
        // is fresh for every test.
        Schema::create('users', function ($table) {
            $table->string('id')->primary();
            $table->string('api_key')->nullable()->index();
            $table->integer('flights')->default(0);
            $table->integer('flight_time')->default(0);
            $table->unsignedBigInteger('rank_id')->nullable();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Modules\StratosLogbook\Providers\AppServiceProvider::class,
            \Modules\StratosLogbook\Providers\RouteServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
