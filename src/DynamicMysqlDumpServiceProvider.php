<?php

namespace Albertoni\DynamicDataBaseBackup;

use Illuminate\Support\ServiceProvider;
use Albertoni\DynamicDataBaseBackup\DumpService;

class DynamicMysqlDumpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('dynamicDump', function($app) {
            return new DumpService();
        });
    }
}