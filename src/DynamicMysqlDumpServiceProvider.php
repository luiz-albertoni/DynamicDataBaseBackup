<?php

namespace Albertoni\DynamicDataBaseBackup;

use Illuminate\Support\ServiceProvider;
use Albertoni\DynamicDataBaseBackup\DynamicMysqlDumpService;

class DynamicMysqlDumpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('dynamicDump', function($app) {
            return new DynamicMysqlDumpService();
        });
    }
}