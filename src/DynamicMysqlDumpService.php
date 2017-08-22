<?php

namespace Albertoni\DynamicMysqlDataBaseBackup;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dotenv\Dotenv;
use ZipArchive;


class DynamicMysqlDumpService
{
    private $process                    = null;
    private $specific_storage_type      = null;
    private $specific_storage_path      = null;
    private $use_zip                    = true;
    private $file_name                  = '';
    public $result                      = true;

    public function runDump()
    {
        try {
            $this->specific_storage_type    = Config::get('dynamic-mysql-dump.specific_storage_type');
            $this->specific_storage_path    = Config::get('dynamic-mysql-dump.specific_storage_path');
            $this->use_zip                  = Config::get('dynamic-mysql-dump.use_zip');

            if ($this->shouldUseDynamicDumpInthisEnviroment()) {
                $this->generateNewDBDump();
            }
        }
        catch(\Exception $e)
        {
            $this->result = false;
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    private function shouldUseDynamicDumpInthisEnviroment()
    {
        $use_for_app_envs         = Config::get('dynamic-mysql-dump.use_for_app_env');
        $currently_env            = getenv('APP_ENV');

        foreach ($use_for_app_envs as $use_for_app_env) {

            if ($currently_env === $use_for_app_env) {
                return  true;
            }
        }

        $this->result = false;
        return false;

    }

    /**
     * @return void
     */
    public function generateNewDBDump()
    {
        $cmd = $this->getDumpCommand();

        $cwd = $this->getLocation();

        $this->createProcess($cmd, $cwd);

        $this->process->run();

        $this->checkSuccessProcessingCommand();
    }

    /**
     * @return void
     */
    public function checkSuccessProcessingCommand()
    {
        if ($this->checkAndLogCommandError()) {

            $this->sendToSpecificStorage();

            $this->removeOldStorage();
        }
    }

    /**
     * @return string
     */
    private function getDumpCommand()
    {
        $database_name  = getenv('DB_DATABASE');
        $password       = getenv('DB_PASSWORD');
        $user_name      = getenv('DB_USERNAME');
        $host           = getenv('DB_HOST');
        $port           = getenv('DB_PORT');

        if ($this->use_zip) {
            $this->file_name=  sprintf('%s-%s.zip', date('Y-m-d'), $database_name );
            $cmd =  sprintf('mysqldump -u %s --password=%s -h %s --port=%s %s | gzip  > %s', $user_name, $password, $host, $port, $database_name,  $this->file_name);
        } else {
            $this->file_name=  sprintf('%s-%s.sql', date('Y-m-d'), $database_name );
            $cmd =  sprintf('mysqldump -u %s --password=%s -h %s --port=%s %s > %s', $user_name, $password, $host, $port, $database_name,  $this->file_name);
        }

        return $cmd;
    }

    /**
     * @return string
     */
    private function getLocation()
    {
        return __DIR__  . '/../temp/';
    }

    /**
     * @param $process
     */
    private function checkAndLogCommandError()
    {
        // executes after the command finishes
        if (!$this->process->isSuccessful()) {
            $error = sprintf(
                'The command "%s" failed.' . "\n\nExit Code: %s(%s)\n\nWorking directory: %s",
                $this->process->getCommandLine(),
                $this->process->getExitCode(),
                $this->process->getExitCodeText(),
                $this->process->getWorkingDirectory()
            );

            $this->result = false;
            Log::error($error);
            return false;
        }
        return true;
    }

    private function sendToSpecificStorage()
    {
        if(isset($this->specific_storage_type)) {
            $path = $this->getLocation() .  $this->file_name;
            $key  = $this->specific_storage_path . $this->file_name;

            Storage::disk($this->specific_storage_type)->put($key, fopen($path, 'r+'));

            $this->deleteTempFiles($path);
        } else {
            $this->result = false;
        }
    }

    private function removeOldStorage()
    {
        if(isset($this->specific_storage_type)) {

            $stored_day = $this->getNumberOfStoreDays();
            $date = date("Y-m-d", strtotime("-".$stored_day." day"));
            $database_name   = getenv('DB_DATABASE');

            $remove_file_key =  ($this->use_zip)?sprintf('%s/%s-%s.zip', $this->specific_storage_path, $date, $database_name ):sprintf('%s/%s-%s.sql', $this->specific_storage_path, $date, $database_name );

            $using_Laravel_5_1  = Config::get('dynamic-mysql-dump.using_Laravel_5_1');

            if($using_Laravel_5_1)
            {
                if( Storage::disk($this->specific_storage_type)->has($remove_file_key))
                {
                    Storage::disk($this->specific_storage_type)->delete($remove_file_key);
                }
            }
            else{
                Storage::disk($this->specific_storage_type)->delete($remove_file_key);
            }
        }
    }

    /**
     * @param $cmd
     * @param $cwd
     * @return Process
     */
    private function createProcess($cmd, $cwd)
    {
        $this->process = new Process($cmd, $cwd);
    }

    public function setProcess($process)
    {
        $this->process = $process;
    }
    /**
     * @return integer
     */
    private function getNumberOfStoreDays()
    {
        $stored_Days  = Config::get('dynamic-mysql-dump.store_days');
        return ($stored_Days) ? $stored_Days : 5;
    }

    /**
     * @param $path
     */
    private function createZipFile($path)
    {
        $zip_path_name = $this->getLocation() . $this->getZipName();

        $zip = new ZipArchive();
        if ($zip->open($zip_path_name, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($path, $this->file_name);
            $zip->close();
        }

        $this->deleteTempFiles($path);

        return $zip_path_name;
    }

    /**
     * @return string
     */
    private function getZipName()
    {
        $database_name = getenv('DB_DATABASE');
        $zip_name = sprintf('%s-%s.zip', date('Y-m-d'), $database_name);
        return $zip_name;
    }

    /**
     * @param $path
     */
    private function deleteTempFiles($path)
    {
        File::delete($path);
    }

}
