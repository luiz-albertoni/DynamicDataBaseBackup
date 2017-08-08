<?php

namespace Albertoni\DynamicDataBaseBackup;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dotenv\Dotenv;


class DumpService
{

    public function runDump()
    {
        $dotenv = new Dotenv(__DIR__ . '/../');
        $dotenv->load();

        $connection_type = getenv('DB_CONNECTION');
        $database_name = getenv('DB_DATABASE');

        $password = getenv('DB_PASSWORD');
        $user_name = getenv('DB_USERNAME');
        $host = getenv('DB_HOST');

        $cwd ='/home/vagrant/';
        $cmd = sprintf('mysqldump -u %s --password=%s -h %s %s > sql_dump_local_7_27_17-demo.sql', $user_name, $password, $host, $database_name);

        echo $cmd;

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
            echo $error;

        }
    }
}