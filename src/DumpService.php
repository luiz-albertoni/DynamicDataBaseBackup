<?php

namespace Albertoni\DynamicDataBaseBackup;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dotenv\Dotenv;


class DumpService
{
    private $process            = null;
    private $specific_storage   = null;
    private $specific_path      = null;
    private $file_name          = '';

    public function runDump($specific_storage = 'local', $specific_path = '')
    {
        try {
            $this->specific_storage = $specific_storage;
            $this->specific_path    = $specific_path;

            $this->generateNewDBDump();
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }


    /**
     * @param $cmd
     * @param $cwd
     */
    private function generateNewDBDump()
    {
        $cmd = $this->getDumpCommand();

        $cwd = $this->getLocation();

        $this->setProcess($cmd, $cwd);

        $this->process->run();

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

        $this->file_name=  sprintf('%s-%s.sql', date('Y-m-d'), $database_name );
        return sprintf('mysqldump -u %s --password=%s -h %s --port=%s %s > %s', $user_name, $password, $host, $port, $database_name,  $this->file_name);
    }

    private function getLocation() {
        return __DIR__  . '/' ;
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
            Log::error($error);
            return false;
        }
        return true;
    }

    private function sendToSpecificStorage() {
        if(isset($this->specific_storage)) {

            $key  = $this->specific_path . $this->file_name;
            $path = $this->getLocation() .  $this->file_name;

            Storage::disk($this->specific_storage)->put($key, fopen($path, 'r+'));
            Storage::disk($this->specific_storage)->delete($path);
        }
    }

    private function removeOldStorage(){
        if(isset($this->specific_storage)) {

            $stored_day= $this->getNumberOfStoreDays();
            $date = strtotime(date("Y-m-d", strtotime("-'.$stored_day.' day")));

            $database_name  = getenv('DB_DATABASE');
            $remove_file_key=  sprintf('%s/%s-%s.sql', $this->specific_path, $date, $database_name );

            Storage::disk($this->specific_storage)->delete($remove_file_key);
        }
    }

    /**
     * @param $cmd
     * @param $cwd
     * @return Process
     */
    private function setProcess($cmd, $cwd)
    {
        $this->process = new Process($cmd, $cwd);
    }

    private function getNumberOfStoreDays() {
        $stored_Days  = getenv('STORE_DAYS');
        return ($stored_Days) ? $stored_Days : 5;
    }

}