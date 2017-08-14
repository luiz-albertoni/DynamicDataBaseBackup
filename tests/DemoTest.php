<?php


use Mockery as m;
use \Illuminate\Support\Facades\Storage;

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
    public function testSomethingIsTrue()
    {

        $jobRepository              = m::mock('Illuminate\Support\Facades\Storage');
        $jobRepository->shouldReceive('disk')->andReturnSelf(null);
        $jobRepository->shouldReceive('put')->andReturn(true);
        $jobRepository->shouldReceive('delete')->andReturn(false);

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('put')->once()->andReturn(true);
        Storage::shouldReceive('delete')->once()->andReturn(true);



        $db = new \Albertoni\DynamicDataBaseBackup\DumpService();
        $db->runDump();
        $this->assertTrue(true);
    }

}