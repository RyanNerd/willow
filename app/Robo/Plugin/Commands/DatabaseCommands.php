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
        $cli = $this->cli;
        if (!file_exists(self::DOT_ENV_FILE)) {
            UserReplies::setEnvFromUser();
        }
        include_once self::DOT_ENV_INCLUDE_FILE;

        // Get the tables from the database
        $tables = DatabaseUtilities::getTableList();
        $tableList = [];
        foreach ($tables as $table) {
            $tableList[] = ['table_name' => $table];
        }

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();
    }

    /**
     * Display column details for a selected table
     */
    final public function details(): void {
        $cli = $this->cli;
        if (!file_exists(self::DOT_ENV_FILE)) {
            UserReplies::setEnvFromUser();
        }
        include_once self::DOT_ENV_INCLUDE_FILE;

        $tables = DatabaseUtilities::getTableList();

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
}
