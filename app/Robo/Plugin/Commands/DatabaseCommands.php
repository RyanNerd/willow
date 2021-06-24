<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

class DatabaseCommands
{
    private const DOT_ENV_FILE = __DIR__ . '/../../../../.env';
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';

    private CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();
    }

    /**
     * Display all the tables in a grid
     */
    final public function tables(): void {
        $this->checkEnvLoaded();

        // Get the tables from the database
        $tables = DatabaseUtilities::getTableList();
        $tableList = [];
        foreach ($tables as $table) {
            $tableList[] = ['table_name' => $table];
        }

        // Display the list of tables in a grid
        $cli = $this->cli;
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();
    }

    /**
     * Display column details for a selected table
     */
    final public function details(): void {
        $this->checkEnvLoaded();

        $tables = DatabaseUtilities::getTableList();

        // Ask the user what table to get details for
        $cli = $this->cli;
        /** @var Input $input */
        $input = $cli->radio('Select a table', $tables);
        $tableName = $input->prompt();

        $details = DatabaseUtilities::getTableAttributes($tableName);

        $displayDetails = [];
        foreach ($details as $column => $type) {
            $displayDetails[] = ['Column' => $column, 'Type' => $type];
        }
        $cli->br();
        $cli->bold()->blue()->table($displayDetails);
        $cli->br();
    }

    /**
     * Check if the .env file exists and has been loaded, if not prompt the user and build the .env file.
     */
    private function checkEnvLoaded() {
        if (!file_exists(self::DOT_ENV_FILE)) {
            $cli = CliBase::getCli();
            CliBase::billboard('welcome', 160, 'top');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('welcome', 160, '-top');
            $cli->clear();

            UserReplies::setEnvFromUser();
        }

        // If the .env file is not loaded then load it now.
        if (strlen($_ENV['DB_DRIVER'] ?? '') === 0) {
            include_once self::DOT_ENV_INCLUDE_FILE;
        }
    }
}
