<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Willow\Robo\Plugin\Commands\Traits\EnvSetupTrait;

class TableCommands extends RoboBase
{
    use EnvSetupTrait;

    public function tables()
    {
        $cli = $this->cli;
        $container = self::_getContainer();

        if (!$container->has('ENV')) {
            $cli->bold()->lightGray("Database configuration hasn't been set up yet.");
            $input = $cli->lightGray()->confirm('Do you want to set up the database configuration now?');
            if ($input->confirmed()) {
                $this->setEnvFromUser();
            } else {
                $cli->bold()->yellow('Unable to show tables without the database configuration set.');
                die();
            }
        }

        // Get Eloquent ORM manager
        $eloquent = $this->getEloquent();

        // Get the tables from the database
        $tableList = DatabaseUtilities::getTableList($eloquent->getConnection());

        // Display the list of tables in a grid
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();
    }
}
