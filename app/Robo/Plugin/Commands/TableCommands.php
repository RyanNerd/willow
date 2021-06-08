<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Confirm;
use Willow\Robo\Plugin\Commands\Traits\EnvSetupTrait;

class TableCommands extends RoboBase
{
    use EnvSetupTrait;

    /**
     * Display all the tables in a grid
     */
    public function tables()
    {
        $cli = $this->cli;
        $this->checkEnv();

        // Get the tables from the database
        $tableList = DatabaseUtilities::getTableList($this->getEloquent()->getConnection());

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();
    }

    /**
     * Display column details for a table
     */
    public function details() {
        $cli = $this->cli;
        $this->checkEnv();

        $eloquent = $this->getEloquent();
        $tableList = DatabaseUtilities::getTableList($eloquent->getConnection());

        $tableChoices = array_column($tableList, 'table_name');
        /** @var Confirm $input */
        $input = $cli->radio('Select a table', $tableChoices);
        $tableName = $input->prompt();

        $details = DatabaseUtilities::getTableAttributes($eloquent, $tableName);

        $displayDetails = [];
        foreach ($details as $column=>$type) {
            $displayDetails[] = ['Column' => $column, 'Type' => $type];
        }
        $cli->br();
        $cli->bold()->blue()->table($displayDetails);
        $cli->br();
    }

    /**
     * Check if .env exists and has been validated. If not prompt the user to set up the configuration now.
     */
    protected function checkEnv() {
        if (!self::_getContainer()->has('ENV')) {
            $this->cli->bold()->lightGray("Database configuration hasn't been set up yet.");
            $input = $this->cli->lightGray()->confirm('Do you want to set up the database configuration now?');
            if ($input->confirmed()) {
                $this->setEnvFromUser();
            } else {
                $this
                    ->cli
                    ->bold()
                    ->yellow('Unable to connect to a database without the database configuration set.');
                die();
            }
        }
    }
}
