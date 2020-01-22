<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Confirm;

class DbCommands extends RoboBase
{
    /**
     * Initialization of the Willow framework specifically the .env file
     */
    public function dbInit(): void
    {
        $cli = $this->cli;

        $envPath = __DIR__ . '/../../../../.env';
        if (file_exists($envPath)) {
            $this->warning('A .env file already exists.');
            $cli->out('Delete the .env file and re-run this command or manually edit the .env file');
            return;
        }

        $cli->bold()->white('Enter values for the .env file');

        if (self::isWindows()) {
            $drivers = ['mysql', 'pgsql', 'sqlsrv', 'sqlite'];

            do {
                $cli->out('Driver must be one of: ' . implode(', ', $drivers));

                /** @var Confirm $input */
                $input = $cli->input('DB_DRIVER (default: mysql)');
                $input->defaultTo('mysql');
                $dbDriver = $input->prompt();
            } while (!in_array($dbDriver, $drivers));
        } else {
            do {
                $drivers = [
                    'MySQL/MariaDB' => 'mysql',
                    'Postgres' => 'pgsql',
                    'MS SQL' => 'sqlsrv',
                    'SQLite' => 'sqlite'
                ];

                $driverChoices = array_keys($drivers);
                /** @var Confirm $input */
                $input = $cli->radio('Select database driver', $driverChoices);
                $driverSelection = $input->prompt();
                $dbDriver = $drivers[$driverSelection];
            } while (strlen($dbDriver) === 0);
        }

        do {
            if ($dbDriver === 'sqlite') {
                $dbHost = '';
                $dbPort = '';
                $dbUser = '';
                $dbPassword = '';
            } else {
                do {
                    $input = $cli->input('DB_HOST (default: 127.0.0.1)');
                    $input->defaultTo('127.0.0.1');
                    $dbHost = $input->prompt();
                } while(strlen($dbHost) === 0);

                do {
                    $input = $cli->input('DB_PORT (default: 3306)');
                    $input->defaultTo('3306');
                    $dbPort = $input->prompt();
                } while(strlen($dbPort) === 0 || (int)$dbPort <= 0 || (int)$dbPort > 65535);

                do {
                    $input = $cli->input('DB_USER');
                    $dbUser = $input->prompt();
                } while(strlen($dbUser) === 0);

                do {
                    $input = $cli->password('DB_PASSWORD');
                    $dbPassword = $input->prompt();
                } while(strlen($dbPassword) === 0);
            }

            do {
                $input = $cli->input('DB_NAME');
                $dbName = $input->prompt();
            } while(strlen($dbName) === 0);

            $input = $cli->input('DISPLAY_ERROR_DETAILS (true/false)');
            $displayErrorDetails = $input
                ->accept(['true', 'false'])
                ->defaultTo('false')
                ->prompt();

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
            $this->warning('Make sure the information is correct.');
            $input = $cli->confirm('Create .env?');
        } while (!$input->confirmed());

        $spinner = $this->cli->white()->spinner('Working');
        foreach (range(0, 2000) as $i) {
            $spinner->advance();
            usleep(500);
        }

        if (file_put_contents($envPath, $envText) !== false) {
            $cli->out('.env file created');
        } else {
            $this->error('Unable to create .env file');
        }
    }

    /**
     * Show all tables in the database
     *
     * @todo Postgres "SELECT table_schema,table_name, table_catalog FROM information_schema.tables WHERE table_catalog = 'CATALOG/SCHEMA HERE' AND table_type = 'BASE TABLE' AND table_schema = 'public' ORDER BY table_name;"
     * @todo SQLite "SELECT `name` FROM sqlite_master WHERE `type`='table'  ORDER BY name";
     * @todo MSSQL "select Table_Name, table_type from information_schema.tables";
     *
     * @see https://stackoverflow.com/questions/33478988/how-to-fetch-the-tables-list-in-database-in-laravel-5-1
     * @see https://stackoverflow.com/questions/29817183/php-mssql-pdo-get-table-names
     */
    public function dbShowTables()
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $tables = $this->getTables();
        foreach ($tables as $table) {
            $this->cli->blue()->bold()->out($table);
        }
    }

    /**
     * Show all views in the database
     */
    public function dbShowViews()
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $views = $this->getViews();
        foreach ($views as $view) {
            $this->cli->blue()->bold()->out($view);
        }
    }

    /**
     * Show column details for a given table
     *
     * @param string $tableName
     */
    public function dbShowColumns(string $tableName)
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $columns = $this->getTableDetails($tableName);
        foreach ($columns as $columnName => $columnType) {
            $this->cli->blue()->bold()->out($columnName . ' => ' . $columnType);
        }
    }
}
