<?php

namespace Albertoni\DynamicDataBaseBackup;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
/**
 * Created by PhpStorm.
 * User: luiz.albertoni
 * Date: 06/08/17
 * Time: 21:13
 */
class DumpService
{

    public function runDump()
    {

        throw new \Exception("Unable to get queue size: " . print_r([], 1));
        $connection_type = env('DB_CONNECTION');
        $database_name = env('DB_DATABASE');

        $password = env('DB_PASSWORD');
        $user_name = env('DB_USERNAME');
        $host = env('DB_HOST');

        $cwd ='/home/vagrant/';
        $cmd = sprintf('mysqldump -u %s --password=%s -h %s %s > sql_dump_local_7_27_17-demo.sql', $user_name, $password, $host, $database_name);

        $process = new Process($cmd, $cwd);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            $error = sprintf(
                'The command "%s" failed.' . "\n\nExit Code: %s(%s)\n\nWorking directory: %s",
                $process->getCommandLine(),
                $process->getExitCode(),
                $process->getExitCodeText(),
                $process->getWorkingDirectory()
            );

        }
    }
}