<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;

final class UserReplies
{
    /**
     * Formerly tableInit()
     * Get the tables the user wants to include in their project
     * @param array<string> $tables
     * @return array<string>
     */
    final public static function getTableSelection(array $tables): array {
        $cli = CliBase::getCli();

        // Get the tables the user wants to add to the project
        do {
            $cli->br();
            do {
                $input = $cli
                    ->lightGreen()
                    ->checkboxes('Select all of the tables you want to add to your project', $tables);
                $selectedTables = $input->prompt();
            } while (count($selectedTables) === 0);

            $displayTables = [];
            foreach ($selectedTables as $table) {
                $displayTables[] = ['Selected Tables' => $table];
            }

            $cli->br();
            $cli->bold()->lightBlue()->table($displayTables);
            $cli->br();

            /** @var Input $input */
            $input = $cli->lightGray()->confirm('This look okay?');
        } while (!$input->confirmed());

        return $selectedTables;
    }

    /**
     * Formerly envInit()
     * Get the .env file settings from the user
     * @return string The .env settings
     */
    final public static function getDotEnv(): string {
        $cli = CliBase::getCli();
        $cli->br();
        $cli->lightGreen()->border('*', 80);
        $cli->bold()->green('Willow uses a .env file to configure database access.');
        $cli->bold()->lightGreen('Run `./willow docs` for more information.');
        $cli->lightGreen()->border('*', 80);
        $cli->br();
        $cli->bold()->white('Enter values for the .env file');

        do {
            $drivers = [
                'MySQL' . extension_loaded('pdo_mysql') ? '' : ' [note: pdo_mysql driver not installed]' => 'mysql',
                'Postgres'  . extension_loaded('pdo_pgsql') ? '' : ' [note: pdo_pgsql driver not installed]' => 'pgsql',
                'MS SQL' . extension_loaded('pdo_sqlsrv') ? '' : ' [note: pdo_sqlsrv driver not installed]' => 'sqlsrv',
                'SQLite' . extension_loaded('pdo_sqlite') ? '' : ' [note: pdo_sqlite driver not installed]' => 'sqlite'
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

            $input = $cli->confirm('Do you want to include model events');
            $modelEvents = $input->confirmed() ? 'true' : 'false';
            $envText = <<<env
# Database configuration
DB_DRIVER=$dbDriver
DB_HOST=$dbHost
DB_PORT=$dbPort
DB_NAME=$dbName
DB_USER=$dbUser
DB_PASSWORD=$dbPassword

# Show error details as a HTML response
DISPLAY_ERROR_DETAILS=$displayErrorDetails

# Use Eloquent's event handling engine
MODEL_EVENTS=$modelEvents

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

    /**
     * Formerly routeInit()
     * @param array $tables
     * @return array
     */
    final public static function getRouteSelection(array $tables): array {
        $cli = CliBase::getCli();

        $tableRouteList = [];
        foreach ($tables as $table) {
            $tableRouteList[] = ['Table' => $table, 'Route' => strtolower($table)];
        }

        $cli->br();
        $cli->white('Routes are defaulted to the lowercase table name.');
        $cli->white('This is what the routes currently look like:');
        $cli->br();
        $cli->bold()->blue()->table($tableRouteList);
        $cli->br();

        $cli->out('You will be prompted to change or keep a route for each table.');
        $input = $cli->input('Press enter to continue.');
        $input->prompt();
        $cli->br();

        do {
            $selectedRoutes = [];
            foreach ($tableRouteList as $item) {
                $table = $item['Table'];
                $route = $item['Route'];

                $input = $cli->input('Table: ' . $table . " Enter Route ($route):");
                $input->defaultTo($route);
                $response = $input->prompt();
                $selectedRoutes[$table] = $response;
            }

            $displayRouteList = [];
            foreach ($selectedRoutes as $table => $route) {
                $displayRouteList[] = ['Table' => $table, 'Route' => strtolower($route)];
            }

            $cli->br();
            $cli->bold()->blue()->table($displayRouteList);
            $cli->br();

            /** @var Input $input */
            $input = $cli->lightGray()->confirm('This look okay?');
        } while (!$input->confirmed());
        return $selectedRoutes;
    }
}
