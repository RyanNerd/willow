<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\TerminalObject\Dynamic\Input;
use Robo\Tasks;

abstract class CommandBase extends Tasks
{
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';
    private const DOT_ENV_PATH = __DIR__ . '/../../../../.env';
    protected const VIRIDIAN_PATH = __DIR__ . '/../../../../.viridian';

    /**
     * Get the tables the user wants to include in their project
     * @param array<string> $tables
     * @return array<string>
     */
    final public static function getMultipleTableSelection(array $tables): array {
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
            $cli->bold()->tbl()->table($displayTables);
            $cli->br();

            /** @var Input $input */
            $input = $cli->lookok()->confirm('This look okay?');
        } while (!$input->defaultTo('y')->confirmed());

        return $selectedTables;
    }

    /**
     * Ask user what table they want to use
     * @return string
     * @throws Exception
     */
    public static function getSingleTableSelection(): string {
        $tables = DatabaseUtilities::getTableList();
        /** @var Input $input */
        $input = CliBase::getCli()->radio('Select a table', $tables);
        return $input->prompt();
    }

    /**
     * Get the .env file settings from the user
     * @return string The .env settings
     */
    private static function getDotEnv(): string {
        $cli = CliBase::getCli();
        do {
            $mySQL = extension_loaded('pdo_mysql') ?
                'MySQL' : 'MySQL <bold><red>[pdo_sql driver not installed]';
            $postgres = extension_loaded('pdo_pgsql') ?
                'Postgres' : 'Postgres <bold><red>[pdo_pgsql driver not installed]';
            $msSQL = extension_loaded('pdo_sqlsrv') ?
                'MS SQL' : 'MS SQL <bold><red>[pdo_sqlsrv driver not installed]';
            $sqlite = extension_loaded('pdo_sqlite') ?
                'SQLite' : 'SQLite <bold><red>[pdo_sqlite driver not installed]';

            $drivers = [
                'mysql' => $mySQL,
                'pgsql' => $postgres,
                'sqlsrv' => $msSQL,
                'sqlite' => $sqlite
            ];

            /** @var Input $input */
            $input = $cli->bold()->green()->radio('Select database driver', $drivers);
            $dbDriver = $input->prompt();
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
            $showErrors = $input->defaultTo('y')->confirmed() ? 'true' : 'false';
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
        } while (!$input->defaultTo('y')->confirmed());
        return $envText;
    }

    /**
     * Check if the .env file exists and if not prompt user to create it then load and validate.
     */
    protected function checkEnvLoaded(): void {
        // Does the .env file not exist? If not then prompt user and create
        if (!file_exists(self::DOT_ENV_PATH)) {
            CliBase::billboard('make-env', 160, 'top');
            $input = CliBase::getCli()->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('make-env', 160, '-top');
            CliBase::getCli()->clear();
            CommandBase::setEnvFromUser();
        }

        // If the .env file is not loaded then load it now.
        if (strlen($_ENV['DB_DRIVER'] ?? '') === 0) {
            include_once self::DOT_ENV_INCLUDE_FILE;
        }
    }

    /**
     * When the .env file does not exist this function is called to prompt the user to create the .env file
     */
    final public static function setEnvFromUser(): void
    {
        $dotEnvFile = __DIR__ . '/../../../../.env';
        while (!file_exists($dotEnvFile)) {
            $envFileContent = self::getDotEnv();
            file_put_contents($dotEnvFile, $envFileContent);
        }
    }

    /**
     * Run the sample
     * This is here because the eject command removes the SampleCommand.php
     */
    protected function runSample() {
        $this->taskServer(8088)
            ->host('127.0.0.1')
            ->dir(__DIR__ . '/../../../../public')
            ->background()
            ->run();
        $this->taskOpenBrowser('http://localhost:8088/v1/sample/Hello-World')->run();
        sleep(15);
    }
}
