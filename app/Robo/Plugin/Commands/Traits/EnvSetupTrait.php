<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

trait EnvSetupTrait
{
    protected CLImate $cli;

    /**
     * Initialization of the .env file
     * @return string
     */
    private function envInit(): string {
        $cli = $this->cli;
        $cli->br();
        $cli->lightGreen()->border('*', 80);
        $cli->bold()->green('Willow uses a .env file to configure database access.');
        $cli->bold()->lightGreen('Run `./willow docs` for more information.');
        $cli->lightGreen()->border('*', 80);
        $cli->br();
        $cli->bold()->white('Enter values for the .env file');

        do {
            $drivers = [
                'MySQL/MariaDB' => 'mysql',
                'Postgres' => 'pgsql',
                'MS SQL' => 'sqlsrv',
                'SQLite' => 'sqlite'
            ];

            $driverChoices = array_keys($drivers);
            /** @var Input $input */
            $input = $cli->radio('Select database driver', $driverChoices);
            $driverSelection = $input->prompt();
            $dbDriver = $drivers[$driverSelection];
        } while (strlen($dbDriver) === 0);

        do {
            if ($dbDriver === 'sqlite') {
                $dbHost = '';
                $dbPort = '';
                $dbUser = '';
                $dbPassword = '';
            } else {
                do {
                    /** @var Input $input */
                    $input = $cli->input('DB_HOST (default: 127.0.0.1)');
                    $input->defaultTo('127.0.0.1');
                    $dbHost = $input->prompt();
                } while (strlen($dbHost) === 0);

                do {
                    /** @var Input $input */
                    $input = $cli->input('DB_PORT (default: 3306)');
                    $input->defaultTo('3306');
                    $dbPort = $input->prompt();
                } while (strlen($dbPort) === 0 || (int)$dbPort <= 0 || (int)$dbPort > 65535);

                do {
                    /** @var Input $input */
                    $input = $cli->input('DB_USER');
                    $dbUser = $input->prompt();
                } while (strlen($dbUser) === 0);

                do {
                    /** @var Input $input */
                    $input = $cli->password('DB_PASSWORD');
                    $dbPassword = $input->prompt();
                } while (strlen($dbPassword) === 0);
            }

            do {
                /** @var Input $input */
                $input = $cli->input('DB_NAME');
                $dbName = $input->prompt();
            } while (strlen($dbName) === 0);

            /** @var Input $input */
            $input = $cli->confirm('DISPLAY_ERROR_DETAILS');
            $displayErrorDetails = $input->confirmed() ? 'true' : 'false';

            $envText = <<<env
DB_DRIVER=$dbDriver
DB_HOST=$dbHost
DB_PORT=$dbPort
DB_NAME=$dbName
DB_USER=$dbUser
DB_PASSWORD=$dbPassword
DISPLAY_ERROR_DETAILS=$displayErrorDetails

env;

            $envText = str_replace("\n\r", "\n", $envText);
            $envLines = explode("\n", $envText);
            $obfuscatedEnv = "";
            foreach ($envLines as $line) {
                if (strlen($line) === 0) {
                    continue;
                }

                if (strstr($line, 'DB_PASSWORD')) {
                    $obfuscatedEnv .= 'DB_PASSWORD=********' . PHP_EOL;
                } else {
                    $obfuscatedEnv .= $line . PHP_EOL;
                }
            }

            $cli->br();
            $cli->bold()->white()->border();
            $cli->white($obfuscatedEnv);
            $cli->bold()->white()->border();
            $cli->br();
            /** @var Input $input */
            $input = $cli->bold()->lightGray()->confirm('This look okay?');
        } while (!$input->confirmed());

        return $envText;
    }
}
