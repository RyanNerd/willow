<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Illuminate\Database\Connection;
use League\CLImate\CLImate;
use Willow\Robo\Plugin\Commands\DatabaseUtilities;

trait TableSetupTrait {
    protected CLImate $cli;

    protected function tableInit(Connection $conn): array {
        $cli = $this->cli;
        $rows = DatabaseUtilities::getTableList($conn);

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($rows);
        $cli->br();

        $input = $cli->input('Press enter to continue');
        $input->prompt();

        $tables = array_column($rows, 'table_name');

        // TODO: Handle Window's choices for this. See: https://climate.thephpleague.com/terminal-objects/input/
        $cli->white('Use the arrow keys to navigate the list of tables, and the spacebar to select');
        $response = '';
        do {
            $input = $cli
                ->lightGreen()
                ->checkboxes('Select the tables you want to add to your project', $tables);
            $selectedTables = $input->prompt();
            if (count($selectedTables) !== 0) {
                $cli->br();
                $cli->bold()->blue()->table($selectedTables);
                $cli->br();

                $input = $cli->lightGray()->input('This look okay?');
                $input->defaultTo('Y');
                $response = $input->accept(['Y', 'N']);
            }
        } while (strtolower($response) !== 'y');

        return $selectedTables;
    }
}
