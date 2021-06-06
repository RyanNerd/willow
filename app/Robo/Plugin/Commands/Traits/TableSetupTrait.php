<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;

trait TableSetupTrait {
    protected CLImate $cli;

    /**
     * @param array $rows
     * @return array<string>
     */
    protected function tableInit(array $tableList): array {
        $cli = $this->cli;

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();

        // Get just the table names as an array
        $tables = array_column($tableList, 'table_name');

        // TODO: Handle Window's choices for this. See: https://climate.thephpleague.com/terminal-objects/input/
        do {
            $cli->br();
            $input = $cli
                ->lightGreen()
                ->checkboxes('Select all of the tables you want to add to your project', $tables);
            $selectedTables = $input->prompt();
            if (count($selectedTables) === 0) {
                continue;
            }

            $displayTables = [];
            foreach ($selectedTables as $table) {
                $displayTables[] = ['Selected Tables' => $table];
            }

            $cli->br();
            $cli->bold()->lightBlue()->table($displayTables);
            $cli->br();

            $input = $cli->lightGray()->confirm('This look okay?');
        } while (!$input->confirmed());

        return $selectedTables;
    }
}
