<?php

/**
 * Created by PhpStorm.
 * User: luiz.albertoni
 * Date: 06/08/17
 * Time: 20:47
 */
class Hello
{

    public function hello(){
        \Illuminate\Support\Facades\Log::info('hello');
        dd('HERE');
    }
}