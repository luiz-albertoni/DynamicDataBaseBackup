<?php

namespace Albertoni\DynamicMysqlDataBaseBackup;

use Illuminate\Support\ServiceProvider;
use Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService;

class DynamicMysqlDumpServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/dynamic-mysql-dump.php' => config_path('dynamic-mysql-dump.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('dynamicDump', function($app) {
            return new DynamicMysqlDumpService();
        });
    }

}