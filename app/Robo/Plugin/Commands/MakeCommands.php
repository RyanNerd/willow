<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

class MakeCommands extends RoboBase
{
    /**
     * Create a model class for a given table name
     *
     * @param string $tableName
     */
    public function makeModel(string $tableName)
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        /** @var CLImate $cli */
        $cli = $this->cli;

        $columns = $this->getTableDetails($tableName);
        if (count($columns) === 0) {
            $this->warning('Table ' . $tableName . ' not found.');
            return;
        }

        do {
            /** @var Input $input */
            $input = $cli->input('What do you want to use as your table alias?');
            // todo: Pascalize the tableName as a suggestion.
            $tableAlias = $input->prompt();
        } while (strlen($tableAlias) < 1);

        $modelTemplate = $this->generateModel($tableName, $tableAlias, $columns);
        $modelPath = __DIR__ . '/../../../../app/Models/' . $tableAlias . '.php';
        if (file_put_contents($modelPath, $modelTemplate) !== false) {
            // Let composer know we added the model class
            $this->taskComposerDumpAutoload();
            $cli->out($tableAlias . ' model created.');
        } else {
            $this->warning('Unable to create model.');
        }
    }

    /**
     * Create a controller given a table alias (model name)
     * 
     * @param string $tableAlias
     */
    public function makeController(string $tableAlias)
    {
        $cli = $this->cli;

        $targetDir =  __DIR__ . '/../../../../app/Controllers/' . $tableAlias;
        if (is_dir($targetDir)) {
            $this->warning('Target directory already exists.');
            return;
        }

        $controllerPath = $targetDir . '/' . $tableAlias . 'Controller.php';
        if (file_exists($controllerPath)) {
            $this->warning($tableAlias . 'Controller already exists.');
            return;
        }

        $defaultRoute = strtolower($tableAlias);
        /** @var Input $input */
        $input = $cli->input('Route?');
        $route = $input->defaultTo($defaultRoute)->prompt();
        if (strlen($route) === 0) {
            $this->error('Invalid route');
            return;
        }

        $controllerTemplate = $this->generateController($tableAlias, $route);
        mkdir($targetDir);

        if(file_put_contents($controllerPath, $controllerTemplate) !== false) {
            $cli->out($tableAlias . 'Controller created.');
            // todo: $this->generateActions()
            // todo: $this->generateValidations()
            // todo: $this->generateGroupRegister()

            // Let composer know we added a controller class
            $this->taskComposerDumpAutoload();
        } else {
            $this->error('Unable to create Controller');
        }

        return;
    }

    private function generateModel($tableName, string $tableAlias, array $tableDetails): string
    {
        $modelTemplate = file_get_contents(__DIR__ . '/Templates/ModelTemplate.php');
        $modelTemplate = str_replace("\n\r", "\n", $modelTemplate);
        $modelTemplate = str_replace('class ModelTemplate', "class $tableAlias", $modelTemplate);
        $modelTemplate = str_replace('TableName', $tableName, $modelTemplate);
        $modelTemplateLines = explode("\n", $modelTemplate);

        $template = '';
        foreach ($modelTemplateLines as $line) {
            if (strpos($line,'* @mixin Builder') > 0) {
                foreach ($tableDetails as $columnName => $columnType) {
                    $template .= ' * @property $' . "$columnName $columnType" . PHP_EOL;
                }
                $template .= ' *' . PHP_EOL;
                $template .= $line . PHP_EOL;
            } else {
                $template .= $line . PHP_EOL;
            }
        }
        return $template;
    }

    private function generateController(string $tableAlias, string $route): string
    {
        $controllerTemplate = file_get_contents(__DIR__ . '/Templates/ControllerTemplate.php');
        $controllerTemplate = str_replace('TableAlias', $tableAlias, $controllerTemplate);
        $controllerTemplate = str_replace('%route%', $route, $controllerTemplate);

        return $controllerTemplate;
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
