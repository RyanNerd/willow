<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;

trait RouteSetupTrait
{
    protected CLImate $cli;

    protected function routeInit(array $tables): array {
        $cli = $this->cli;

        $tableRouteList = [];
        foreach ($tables as $table) {
            $tableRouteList[] = ['Table' => $table, 'Route' => strtolower($table)];
        }

        $cli->white('Routes are defaulted to the lowercase table name.');
        $cli->white('This is what the routes currently look like:');
        $cli->br();
        $cli->bold()->blue()->table($tableRouteList);
        $cli->br();
        $cli->white('You will be prompted to change or keep a route for each table.');
        $input = $cli->input('Press enter to continue.');
        $input->prompt();

        $selectedRoutes = [];
        foreach ($tableRouteList as $item) {
            $table = $item['Table'];
            $route = $item['Route'];

            $input = $cli->input($table);
            $input->defaultTo($route);
            $response = $input->prompt();
            $selectedRoutes[$table] = $response;
        }

        $cli->blue()->table($selectedRoutes);

        $input = $cli->input('Press enter to continue.');
        $input->prompt();

        return $tableRouteList;
    }
}
