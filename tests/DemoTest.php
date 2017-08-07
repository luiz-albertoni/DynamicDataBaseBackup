<?php

/**
 * Created by PhpStorm.
 * User: luiz.albertoni
 * Date: 06/08/17
 * Time: 20:50
 */
class DemoTest extends TestCase
{
    /**
     * @test
     */
    public function testSomethingIsTrue()
    {
        echo 'Blin Blin';

        $db = new \Albertoni\DynamicDataBaseBackup\DumpService();
        $db->runDump();
        $this->assertTrue(true);
    }
}