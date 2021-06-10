<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

trait RouteSetupTrait
{
    protected CLImate $cli;

    protected function routeInit(array $tables): array {
        $cli = $this->cli;

        $tableRouteList = [];
        foreach ($tables as $table) {
            $tableRouteList[] = ['Table' => $table, 'Route' => strtolower($table)];
        }

        $cli->br();
        $cli->white('Routes are defaulted to the lowercase table name.');
        $cli->white('This is what the routes currently look like:');
        $cli->br();
        $cli->bold()->blue()->table($tableRouteList);
        $cli->br();

        $cli->out('You will be prompted to change or keep a route for each table.');
        $input = $cli->input('Press enter to continue.');
        $input->prompt();
        $cli->br();

        do {
            $selectedRoutes = [];
            foreach ($tableRouteList as $item) {
                $table = $item['Table'];
                $route = $item['Route'];

                $input = $cli->input('Table: ' . $table . " Enter Route ($route):");
                $input->defaultTo($route);
                $response = $input->prompt();
                $selectedRoutes[$table] = $response;
            }

            $displayRouteList = [];
            foreach ($selectedRoutes as $table => $route) {
                $displayRouteList[] = ['Table' => $table, 'Route' => strtolower($route)];
            }

            $cli->br();
            $cli->bold()->blue()->table($displayRouteList);
            $cli->br();

            /** @var Input $input */
            $input = $cli->lightGray()->confirm('This look okay?');
        } while (!$input->confirmed());
        return $selectedRoutes;
    }
}
