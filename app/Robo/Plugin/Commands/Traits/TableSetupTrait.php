<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

trait TableSetupTrait
{
    protected CLImate $cli;

    /**
     * @param array $rows
     * @return array<string>
     */
    private function tableInit(array $tableList): array {
        $cli = $this->cli;

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();

        // Get just the table names as an array
        $tables = array_column($tableList, 'table_name');

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
}
