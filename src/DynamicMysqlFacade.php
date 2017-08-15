<?php

namespace Albertoni\DynamicMysqlDataBaseBackup;

use Illuminate\Support\Facades\Facade;


class DynamicMysqlFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dynamicDump';
    }

}