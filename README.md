# DynamicMysqlDataBaseBackup


## Overview

The DynamicMysqlDatabaseBackup is a Laravel package to dynamically take a dump from your Mysql database and 
storage to a filesystem (local filesystems, Amazon S3, and Rackspace Cloud Storage).
It also allow the user to configurate how many days the dump file should remain in the storage, 
so it dynamically storage and remove the backup files through Laravel's Scheduler.


## Install

Composer install

~~~
composer require luiz-albertoni/dynamic-mysql-db-backup
~~~

Edit app/config/app.php to add the DynamicMysqlDumpServiceProvider under the 'providers' Array

~~~
Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpServiceProvider::class,
~~~

Publish the configuration

~~~
php artisan vendor:publish --provider="Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpServiceProvider" --tag='config' --force
~~~

Set Scheduler to run the package. Add the code below in App\Console\Kernel inside the schedule method.

~~~
 $schedule->call(function () {
            DynamicMysqlFacade::runDump();
        })->daily();
~~~

## Configuration File
  Config file name : dynamic-mysql-dump.php 
  
~~~  
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Dynamic Mysql Dump Configuration
    |--------------------------------------------------------------------------
    */

    'store_days' => 5,

    'specific_storage_type' => 'local',

    'specific_storage_path' => '',
];
~~~

What means each configuration var?

 - store_days :           Number of days which we want to hold the dump file. Default value = 5

 - specific_storage_type:  Which filesystem we are using for storage teh dump file.  Default value =  local

 - specific_storage_path: Specific path that we want to store the dump file in our filesystem.   Default value =  ''

## Dependencies 

To use this package we are assuming:
 - The application is using the Laravel Framework
 - The application has a Mysql database installed
 - The application is using one FileSystem  ( https://laravel.com/docs/5.4/filesystem )
 - The keys below exist in the .env file:
    DB_PASSWORD, DB_USERNAME, DB_DRIVER, DB_HOST, DB_DATABASE.
    
    
## Advices
 In the Scheduler Class do not forget to:
  - Import the DynamicMysqlFacade
  - Make sure already configurate the Cron entry to your server ( https://laravel.com/docs/5.4/scheduling )

## To do
 -  Create a command to run the scheduler, beside use the Facade
 -  Expand this code to handle different databases;

