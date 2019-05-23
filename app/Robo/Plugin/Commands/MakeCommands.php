<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;

class MakeCommands extends RoboBase
{
    /**
     * Create a Model, Controller, and Actions for all selected entities (tables/views)
     */
    public function makeAll()
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $tables = $this->getTables();
        $views = $this->getViews();
        $entities = array_merge($views, $tables);

        $cli = $this->cli;

        // Are we using Windows?
        if (self::isWindows()) {
            // CLIMATE does not support checkboxes in Windows so prompt the user entity by entity instead.
            $selectedEntities = [];
            foreach ($entities as $entity) {
                $input = $cli->confirm('Generate for: ' . $entity);
                if ($input->confirmed()) {
                    $selectedEntities[] = $entity;
                }
            }
        } else {
            $input = $cli->checkboxes('Select the entities you want to generate', $entities);
            $selectedEntities = $input->prompt();
        }

        // Do we have at least one selected entity?
        if (count($selectedEntities) === 0) {
            $this->warning('You did not make any selections.');
            return;
        }

        // Iterate through all selected entities
        foreach($selectedEntities as $entity) {
            $tableAlias = str_replace('_', '', ucwords($entity, '_'));

            // Create Models for selected entities
            $message = $this->createModel($entity);
            if ($message === '') {
                $cli->out("Model $tableAlias created for $entity");
            } else {
                $this->warning($message);
            }

            // Create Controllers for selected entities
            $message = $this->createController($entity);
            if ($message === '') {
                $cli->out($tableAlias . 'Controller created');
            } else {
                $this->warning($message);
            }

            // Create WriteValidators for selected entities
            $message = $this->createWriteValidator($entity);
            if ($message === '') {
                $cli->out($tableAlias . 'WriteValidator created');
            } else {
                $this->warning($message);
            }

            // Create Actions for selected entities
            $message = $this->createActions($entity);
            if ($message === '') {
                $cli->out("Actions created for $tableAlias");
            } else {
                $this->warning($message);
            }
        }
    }

    /**
     * Create a Model for a given table name
     *
     * @param string $tableName
     */
    public function makeModel(string $tableName)
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        /** @var CLImate $cli */
        $cli = $this->cli;
        $message = $this->createModel($tableName);
        if ($message !== '') {
            $this->warning($message);
        } else {
            $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
            $cli->out("Model $tableAlias created for $tableName");
        }
    }

    /**
     * Create a controller given a table/view name
     * 
     * @param string $tableName
     */
    public function makeController(string $tableName)
    {
        $cli = $this->cli;
        $message = $this->createController($tableName);
        if ($message === '') {
            $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
            $cli->out($tableAlias . 'Controller created');
        } else {
            $this->warning($message);
        }

        // Create Validators for the table
        $message = $this->createWriteValidator($tableName);
        if ($message === '') {
            $cli->out($tableAlias . 'WriteValidator created');
        } else {
            $this->warning($message);
        }
    }

    /**
     * Create Get, Patch, Post, and Delete actions for the given table name
     * @param string $tableName
     */
    public function makeActions(string $tableName)
    {
        $cli = $this->cli;
        $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
        $message = $this->createActions($tableName);
        if ($message === '') {
            $cli->out("Actions created for $tableAlias");
        } else {
            $this->warning($message);
        }
    }

    /**
     * Proxy to Model Generator
     *
     * @param string $tableName
     * @return string
     */
    private function createModel(string $tableName): string
    {
        $columns = $this->getTableDetails($tableName);
        if (count($columns) === 0) {
            return "$tableName is not a valid entity (table/view)";
        }

        $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
        $modelPath = __DIR__ . '/../../../../app/Models/' . $tableAlias . '.php';

        // Bail if the Model already exists
        if (file_exists($modelPath)) {
            return "Model $tableAlias already exists.";
        }

        $modelTemplate = $this->generateModel($tableName, $tableAlias, $columns);

        // Did we successfully create the Model?
        if (file_put_contents($modelPath, $modelTemplate) !== false) {
            return '';
        } else {
            return "Unable to generate model for: $tableName";
        }
    }

    private function generateModel($tableName, string $tableAlias, array $tableDetails): string
    {
        $modelTemplate = file_get_contents(__DIR__ . '/Templates/ModelTemplate.php');
        $modelTemplate = str_replace("\n\r", "\n", $modelTemplate);
        $modelTemplate = str_replace('class ModelTemplate', "class $tableAlias", $modelTemplate);
        $modelTemplate = str_replace('TableName', $tableName, $modelTemplate);
        $modelTemplateLines = explode("\n", $modelTemplate);

        $fields = '';
        foreach ($tableDetails as $columnName => $columnType) {
            $fields .= "        '$columnName' => '$columnType'," . PHP_EOL;
        }
        $fieldList = <<<fields
    public const FIELDS = [
$fields
    ];
fields;

        $template = '';
        foreach ($modelTemplateLines as $line) {
            if (strstr($line, 'public const FIELDS = [];')) {
                $template .= $fieldList . PHP_EOL;
                continue;
            }

            if (strstr($line,'* @mixin Builder')) {
                foreach ($tableDetails as $columnName => $columnType) {
                    if ($columnType === 'datetime') {
                        $columnType = '\DateTime';
                    }
                    if ($columnType === 'decimal') {
                        $columnType = 'float';
                    }
                    $template .=" * @property $columnType $" . $columnName . PHP_EOL;
                }
                $template .= ' *' . PHP_EOL;
                $template .= $line . PHP_EOL;
            } else {
                $template .= $line . PHP_EOL;
            }
        }
        return $template;
    }

    /**
     * Proxy to generateController
     *
     * @param string $tableName
     * @return string
     */
    private function createController(string $tableName): string
    {
        $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
        $targetDir =  __DIR__ . '/../../../../app/Controllers/' . $tableAlias;
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        $controllerPath = $targetDir . '/' . $tableAlias . 'Controller.php';
        if (file_exists($controllerPath)) {
            return $tableAlias . 'Controller already exists.';
        }

        $controllerTemplate = $this->generateController($tableAlias, strtolower($tableName));

        if (file_put_contents($controllerPath, $controllerTemplate) !== false) {
            return '';
        } else {
            return "Unable to create $tableAlias Controller";
        }
    }

    private function generateController(string $tableAlias, string $route): string
    {
        $controllerTemplate = file_get_contents(__DIR__ . '/Templates/ControllerTemplate.php');
        $controllerTemplate = str_replace('TableAlias', $tableAlias, $controllerTemplate);
        $controllerTemplate = str_replace('%route%', $route, $controllerTemplate);

        return $controllerTemplate;
    }

    /**
     * Proxy to:
     *  generateGetAction
     *  generatePostAction
     *  generatePatchAction
     *  generateDeleteAction
     *
     * @param string $tableName
     * @return string
     */
    private function createActions(string $tableName): string
    {
        $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
        $targetDir =  __DIR__ . '/../../../../app/Controllers/' . $tableAlias;
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        $getActionPath = $targetDir . '/' . $tableAlias . 'GetAction.php';
        if (file_exists($getActionPath)) {
            return $tableAlias . 'GetAction already exists.';
        }

        $getActionTemplate = $this->generateGetAction($tableAlias);
        if (file_put_contents($getActionPath, $getActionTemplate) === false) {
            return 'Unable to create ' . $tableAlias . 'GetAction.php';
        }

        $postActionPath = $targetDir . '/' . $tableAlias . 'PostAction.php';
        if (file_exists($postActionPath)) {
            return $tableAlias . 'PostAction already exists.';
        }

        $postActionTemplate = $this->generatePostAction($tableAlias);
        if (file_put_contents($postActionPath, $postActionTemplate) === false) {
            return 'Unable to create ' . $tableAlias . 'PostAction.php';
        }

        $patchActionPath = $targetDir . '/' . $tableAlias . 'PatchAction.php';
        if (file_exists($patchActionPath)) {
            return $tableAlias . 'PatchAction already exists.';
        }

        $patchActionTemplate = $this->generatePatchAction($tableAlias);
        if (file_put_contents($patchActionPath, $patchActionTemplate) === false) {
            return 'Unable to create ' . $tableAlias . 'PatchAction.php';
        }

        $deleteActionPath = $targetDir . '/' . $tableAlias . 'DeleteAction.php';
        if (file_exists($deleteActionPath)) {
            return $tableAlias . 'DeleteAction already exists.';
        }

        $deleteActionTemplate = $this->generateDeleteAction($tableAlias);
        if (file_put_contents($deleteActionPath, $deleteActionTemplate) === false) {
            return 'Unable to create ' . $tableAlias . 'DeleteAction.php';
        }

        return '';
    }

    private function generateGetAction(string $tableAlias): string
    {
        $getActionTemplate = file_get_contents(__DIR__ . '/Templates/GetActionTemplate.php');
        return str_replace('TableAlias', $tableAlias, $getActionTemplate);
    }

    private function generatePostAction(string $tableAlias): string
    {
        $postActionTemplate = file_get_contents(__DIR__ . '/Templates/PostActionTemplate.php');
        return str_replace('TableAlias', $tableAlias, $postActionTemplate);
    }

    private function generatePatchAction(string $tableAlias): string
    {
        $patchActionTemplate = file_get_contents(__DIR__ . '/Templates/PatchActionTemplate.php');
        return str_replace('TableAlias', $tableAlias, $patchActionTemplate);
    }

    private function generateDeleteAction(string $tableAlias): string
    {
        $deleteActionTemplate = file_get_contents(__DIR__ . '/Templates/DeleteActionTemplate.php');
        return str_replace('TableAlias', $tableAlias, $deleteActionTemplate);
    }

    /**
     * Proxy to generateWriteValidator
     *
     * @param string $tableName
     * @return string
     */
    private function createWriteValidator(string $tableName): string
    {
        $tableAlias = str_replace('_', '', ucwords($tableName, '_'));
        $targetDir =  __DIR__ . '/../../../../app/Controllers/' . $tableAlias;
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        $validatorPath = $targetDir . '/' . $tableAlias . 'WriteValidator.php';
        if (file_exists($validatorPath)) {
            return $tableAlias . 'WriteValidator already exists.';
        }

        $validatorTemplate = $this->generateWriteValidator($tableAlias);

        if (file_put_contents($validatorPath, $validatorTemplate) !== false) {
            return '';
        } else {
            return 'Unable to create $tableAlias' . 'WriteValidator';
        }
    }

    private function generateWriteValidator(string $tableAlias): string
    {
        $validatorTemplate = file_get_contents(__DIR__ . '/Templates/WriteValidatorTemplate.php');
        $validatorTemplate = str_replace('TableAlias', $tableAlias, $validatorTemplate);
        return $validatorTemplate;
    }
}
