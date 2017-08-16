<?php


use Mockery as m;
use \Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DemoTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
        $dotenv->load();

    }

    /**
     * @test
     */
    public function should_send_file_to_specific_storage()
    {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('put')->once()->andReturn(true);
        Storage::shouldReceive('delete')->once()->andReturn(true);

        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_type')->andReturn('local');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_path')->andReturn('');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.store_days')->andReturn('');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_zip')->andReturn(false);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_for_app_env')->andReturns(['local', 'dev', 'prod']);

        File::shouldReceive('delete')->once()->andReturnSelf();

        Log::shouldReceive('info')->andReturnSelf();
        Log::shouldReceive('error')->andReturnSelf();

        $db = new \Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService();
        $db->runDump();
        $this->assertTrue($db->result);
    }

    /**
     * @test
     */
    public function happy_path()
    {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('put')->once()->andReturn(true);
        Storage::shouldReceive('delete')->once()->andReturn(true);

        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_for_app_env')->andReturns(['local', 'dev', 'prod']);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_zip')->andReturn(false);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_type')->andReturn('local');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_path')->andReturn('');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.store_days')->andReturn('');

        File::shouldReceive('delete')->andReturnSelf();

        Log::shouldReceive('info')->andReturnSelf();
        Log::shouldReceive('error')->andReturnSelf();
        $db = new \Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService();
        $db->runDump();
        $this->assertTrue($db->result);
    }

    /**
     * @test
     */
    public function should_not_send_file_to_specific_storage()
    {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('put')->once()->andReturn(true);
        Storage::shouldReceive('delete')->once()->andReturn(true);

        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_type')->andReturn(null);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_path')->andReturn('');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_for_app_env')->andReturns(['local', 'dev', 'prod']);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_zip')->andReturn(false);

        File::shouldReceive('delete')->once()->andReturnSelf();

        Log::shouldReceive('info')->andReturnSelf();
        Log::shouldReceive('error')->twice()->andReturnSelf();

        $db = new \Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService();
        $db->runDump();

        $this->assertFalse($db->result);
    }


    /**
     * @test
     */
    public function should_fail_in_process_command()
    {
        Config::shouldReceive('get')->andReturnSelf();
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_for_app_env')->andReturns(['local', 'dev', 'prod']);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_zip')->andReturn(false);

        File::shouldReceive('delete')->andReturnSelf();

        Log::shouldReceive('info')->andReturnSelf();
        Log::shouldReceive('error')->andReturnSelf();


        $mock = Mockery::mock(Symfony\Component\Process\Process::class);
        $mock->shouldReceive('getCommandLine')->andReturn('mysql');
        $mock->shouldReceive('getExitCode')->andReturnSelf();
        $mock->shouldReceive('getExitCodeText')->andReturnSelf();
        $mock->shouldReceive('getWorkingDirectory')->andReturnSelf();
        $mock->shouldReceive('isSuccessful')->andReturn(false);


        $db = new \Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService();
        $db->setProcess($mock);
        $db->checkSuccessProcessingCommand();
        $this->assertFalse( $db->result);
    }

    /**
     * @test
     */
    public function should_not_dynamic_dump_in_this_enviroment()
    {
        Config::shouldReceive('get')->with('dynamic-mysql-dump.use_for_app_env')->andReturns(['banana', 'apple']);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.use_zip')->andReturn(false);
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_type')->andReturn('local');
        Config::shouldReceive('get')->once()->with('dynamic-mysql-dump.specific_storage_path')->andReturn('');

        $db = new \Albertoni\DynamicMysqlDataBaseBackup\DynamicMysqlDumpService();
        $db->runDump();
        $this->assertFalse($db->result);
    }
}
