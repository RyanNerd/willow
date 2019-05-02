<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;

class InitCommands extends RoboBase
{
    /**
     * Initialization of the Willow framework specifically two files: .gitignore & .env
     */
    public function willowInit()
    {
        $cli = $this->cli;

        $gitIgnorePath = __DIR__ . '/../../../../.gitignore';
        if (!file_exists($gitIgnorePath)) {
            $this->warning('.gitignore does not exist.');
            $input = $cli->confirm('Create .gitignore?');
            if ($input->confirmed()) {
                file_put_contents($gitIgnorePath, '.env');
            }
        } else {
            $gitIgnore = file_get_contents($gitIgnorePath);
            if (strpos($gitIgnore, '.env') === false) {
                $this->warning('.gitignore does not include .env');
                $input = $cli->confirm('Add .env to .gitignore?');
                if ($input->confirmed()) {
                    file_put_contents($gitIgnorePath, PHP_EOL . '.env' .PHP_EOL, FILE_APPEND);
                }
            }
        }

        $envPath = __DIR__ . '/../../../../.env';
        if (file_exists($envPath)) {
            $this->warning('A .env file already exists.');
            /** @var Input $input */
            $input = $cli->confirm('Do you want to enter new values for .env?');
            $input->defaultTo('n');
            if ($input->confirmed()) {
                $tryAgain = true;
                while($tryAgain) {
                    $cli->bold()->white('Enter values for the .env file');
                    $input = $cli->input('DB_HOST (ex: 127.0.0.1)');
                    $dbHost = $input->prompt();
                    $input = $cli->input('DB_PORT (ex: 3306)');
                    $dbPort = $input->prompt();
                    // TODO; validate port
                    $input = $cli->input('DB_NAME');
                    $dbName = $input->prompt();
                    $input = $cli->input('DB_USER');
                    $dbUser = $input->prompt();
                    $input = $cli->password('DB_PASSWORD');
                    $dbPassword = $input->prompt();
                    $input = $cli->input('DISPLAY_ERROR_DETAILS (true/false)');
                    $displayErrorDetails = $input
                        ->accept(['true', 'false'])
                        ->defaultTo('false')
                        ->prompt();
                    $envText = <<<env
DB_HOST=$dbHost
DB_PORT=$dbPort
DB_NAME=$dbName
DB_USER=$dbUser
DB_PASSWORD=$dbPassword
DISPLAY_ERROR_DETAILS=$displayErrorDetails
env;

                    $cli->br();
                    $cli->bold()->white()->border();
                    $cli->white($envText);
                    $cli->bold()->white()->border();
                    $cli->br();
                    $this->warning('This will completely replace your existing .env file');
                    $input = $cli->confirm('Create .env');
                    if ($input->confirmed()) {
                        $tryAgain = false;
                    }
                }

                $spinner = $this->cli->backgroundLightRed()->white()->spinner('Working');
                foreach (range(0, 10000) as $i) {
                    $spinner->advance();
                    usleep(500);
                }
            } else {
                $cli->bold()->white('TODO: Set up initialization');
            }
        }
    }

    public function block()
    {
        $this->cli->output('block');
    }
}