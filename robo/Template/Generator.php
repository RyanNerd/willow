<?php
declare(strict_types=1);

namespace Robo\Template;

use League\CLImate\CLImate;

Trait Generator
{
    public function addTable(string $tableName, string $tableAlias = "", $route = "")
    {
        if (!$this->isOK()) return;

        /** @var CLImate $cli */
        $cli = $this->cli;

        if (strlen($tableAlias) === 0) {
            $tableAlias = $tableName;
        }

        if (strlen($route) === 0) {
            $route = strtolower($tableAlias);
        }

        if ($this->generateController($tableAlias, $route)) {
            $cli->info()->inline('INFO: ');
            $cli->blue($tableAlias . 'Controller created.');
            $this->generateGroupRegister();
        }
    }

    private function generateController(string $tableAlias, string $route): bool
    {
        /** @var CLImate $cli */
        $cli = $this->cli;

        $controllerTemplate = file_get_contents(__DIR__ . '/../../templates/TemplateController.php');
        $controllerTemplate = str_replace('TableAlias', $tableAlias, $controllerTemplate);
        $controllerTemplate = str_replace('/route', '/' .$route, $controllerTemplate);

        $targetDir =  __DIR__ . '/../../app/' . $tableAlias;
        if (is_dir($targetDir)) {
            $cli->bold()->yellow()->inline('WARNING: ');
            $cli->bold()->lightRed('Target directory already exists.');
            return false;
        }

        mkdir($targetDir);

        file_put_contents($targetDir . '/' . $tableAlias . 'Controller.php', $controllerTemplate);
        return true;
    }

    private function generateGroupRegister(): bool
    {
        /** @var CLImate $cli */
        $cli = $this->cli;

        $mainAppPath =  __DIR__ . '/../../app/Main/App.php';
        $mainApp = file_get_contents($mainAppPath);
        $mainApp = str_replace("\n\r", "\n", $mainApp);
        $main = explode("\n", $mainApp);

        for($i=0;$i < count($main);$i++) {
            $line = $main[$i];
            if (strpos($line, '%DO NOT REMOVE THIS COMMENT% RouteGroupStart') > 0) {
                $cli->info()->inline('INFO: ');
                $cli->blue($line);
                return true;
            }
        }

        return true;
//        $v1 = $app->group('/v1', function (RouteCollectorProxy $collectorProxy) use ($container)
//        {
//            $container->get(\Willow\Hello\HelloController::class)->register($collectorProxy);
//        });
    }
}
