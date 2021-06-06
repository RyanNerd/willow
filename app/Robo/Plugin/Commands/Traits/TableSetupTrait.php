<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;

trait TableSetupTrait {
    protected CLImate $cli;

    protected function tableInit(array $rows): array {
        $cli = $this->cli;

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($rows);
        $cli->br();

        // Get just the table names as an array
        $tables = array_column($rows, 'table_name');

        // TODO: Handle Window's choices for this. See: https://climate.thephpleague.com/terminal-objects/input/
        $response = '';
        do {
            $input = $cli
                ->lightGreen()
                ->checkboxes('Select all of the tables you want to add to your project', $tables);
            $selectedTables = $input->prompt();
            if (count($selectedTables) !== 0) {
                $displayTables = [];
                foreach ($selectedTables as $table) {
                    $displayTables[] = ['Selected Tables' => $table];
                }

                $cli->br();
                $cli->bold()->lightBlue()->table($displayTables);
                $cli->br();

                $input = $cli->lightGray()->input('This look okay? (Y/n)');
                $input->defaultTo('Y');
                $input->accept(['Y', 'N']);
                $response = $input->prompt();
            }
        } while (strtolower($response) !== 'y');

        return $selectedTables;
    }
}
