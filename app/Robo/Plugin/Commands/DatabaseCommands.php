<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

class DatabaseCommands
{
    private CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();
    }

    /**
     * Display all the tables in a grid
     */
    final public function tables(): void {
        $cli = $this->cli;
        $this->checkEnv();

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
        $this->checkEnv();

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

    /**
     * Check if .env exists and if not prompt user for .env contents
     */
    private function checkEnv(): void {
        $dotEnvFile = __DIR__ . '/../../../../.env';
        while (!file_exists($dotEnvFile)) {
            $envFileContent = UserReplies::getDotEnv();
            file_put_contents($dotEnvFile, $envFileContent);
        }
    }
}
