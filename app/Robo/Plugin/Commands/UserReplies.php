<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;

final class UserReplies extends CommandsBase
{
    /**
     * When the .env file does not exist this function is called to prompt the user to create the .env file
     */
    public static function setEnvFromUser(): void {
        $dotEnvFile = __DIR__ . '/../../../../.env';
        while (!file_exists($dotEnvFile)) {
            $envFileContent = self::getDotEnv();
            file_put_contents($dotEnvFile, $envFileContent);
        }
    }

    /**
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
     * Get the .env file settings from the user
     * @return string The .env settings
     */
    private static function getDotEnv(): string {
        $cli = CliBase::getCli();
        $cli->bold()->green('');
        do {
            $mySQL = extension_loaded('pdo_mysql') ? 'MySQL' : 'MySQL [pdo_sql driver not installed]';
            $postgres = extension_loaded('pdo_pgsql') ? 'Postgres' : 'Postgres [pdo_pgsql driver not installed]';
            $msSQL = extension_loaded('pdo_sqlsrv') ? 'MS SQL' : 'MS SQL [pdo_sqlsrv driver not installed]';
            $sqlite = extension_loaded('pdo_sqlite') ? 'SQLite' : 'SQLite [pdo_sqlite driver not installed]';
            $drivers = [
                $mySQL => 'mysql',
                $postgres => 'pgsql',
                $msSQL => 'sqlsrv',
                $sqlite => 'sqlite'
            ];

            $driverChoices = array_keys($drivers);
            /** @var Input $input */
            $input = $cli->radio('Select database driver', $driverChoices);
            $driverSelection = $input->prompt();
            $dbDriver = $drivers[$driverSelection];
        } while (strlen($dbDriver) === 0);

        do {
            $cli->br();
            if ($dbDriver === 'sqlite') {
                $dbHost = '';
                $dbPort = '';
                $dbUser = '';
                $dbPassword = '';
            } else {
                do {
                    /** @var Input $input */
                    $input = $cli->bold()->green()->input('DB_HOST (default: 127.0.0.1)');
                    $input->defaultTo('127.0.0.1');
                    $dbHost = $input->prompt();
                } while (strlen($dbHost) === 0);

                do {
                    /** @var Input $input */
                    $input = $cli->bold()->green()->input('DB_PORT (default: 3306)');
                    $input->defaultTo('3306');
                    $dbPort = $input->prompt();
                } while (strlen($dbPort) === 0 || (int)$dbPort <= 0 || (int)$dbPort > 65535);

                do {
                    /** @var Input $input */
                    $input = $cli->bold()->green()->input('DB_USER');
                    $dbUser = $input->prompt();
                } while (strlen($dbUser) === 0);

                do {
                    /** @var Input $input */
                    $input = $cli->bold()->green()->password('DB_PASSWORD');
                    $dbPassword = $input->prompt();
                } while (strlen($dbPassword) === 0);
            }

            do {
                /** @var Input $input */
                $input = $cli->bold()->green()->input('DB_NAME');
                $dbName = $input->prompt();
            } while (strlen($dbName) === 0);

            /** @var Input $input */
            $input = $cli->bold()->green()->confirm('SHOW_ERRORS');
            $showErrors = $input->confirmed() ? 'true' : 'false';
            $envText = <<<env

# Database configuration
DB_DRIVER=$dbDriver
DB_HOST=$dbHost
DB_PORT=$dbPort
DB_NAME=$dbName
DB_USER=$dbUser
DB_PASSWORD=$dbPassword

# Show error details as a HTML response
SHOW_ERRORS=$showErrors
env;

            $envText = str_replace("\n\r", "\n", $envText);
            $envLines = explode("\n", $envText);
            $obfuscatedEnv = "";
            foreach ($envLines as $line) {
                if (strlen($line) === 0) {
                    $line = ' ';
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
     * @param array $tables
     * @return array
     * @deprecated
     */
    final public static function showSelection(array $tableName): array {
        $cli = CliBase::getCli();

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
