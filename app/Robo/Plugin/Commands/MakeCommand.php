<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Illuminate\Support\Str;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;
use Throwable;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

class MakeCommand extends CommandBase
{
    private const PROGRESS_STAGES = [
        'Model',
        'Controller',
        'Actions',
        'Validators',
        ''
    ];

    private CLImate $cli;

    /**
     * Build the project using database tables
     */
    final public function make(): void {
        try {
            $this->cli = CliBase::getCli();

            // Check for a previous project build out
            $this->checkViridian();

            // Check if .env has been loaded
            $this->checkEnvLoaded();

            $cli = $this->cli;
            CliBase::billboard('make-tables', 165, 'bottom');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('make-tables', 165, '-top');
            $cli->clear();

            // Get the list of tables the user wants in their project
            $selectedTables = CommandBase::getMultipleTableSelection(DatabaseUtilities::getTableList());

            CliBase::billboard('make-routes', 165, 'top');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('make-routes', 300, '-left');

            // Prompt the user for the route for each table.
            do {
                $cli->br(2);
                $routeList = [];
                foreach ($selectedTables as $table) {
                    $route = str_replace('_', '-', Str::snake($table));
                    $cli->br();
                    $cli->bold()->green()->border('*');
                    $cli->bold("<green>Table:  <white>$table");
                    $cli->bold()->green()->border('*');
                    $cli->br();
                    $input = $cli->input("<bold><green>Route <bold><white>($route):");
                    $input->defaultTo($route);
                    $response = $input->prompt();
                    $routeList[$table] = strtolower($response);
                }

                $displayRoutes = [];
                foreach ($routeList as $table => $route) {
                    $displayRoutes[] = ['Table' => $table, 'Base Route' => $route];
                }
                $cli->br();
                $cli->tableFormat()->table($displayRoutes);
                /** @var Input $input */
                $input = $cli->lookok()->confirm('This look okay?');
            } while (!$input->defaultTo('y')->confirmed());

            // Instantiate dependencies for build-out
            $loader = new FilesystemLoader(__DIR__ . '/Templates');
            $twig = new Twig($loader);
            $actionsForge = new ForgeActions($twig);
            $controllerForge = new ForgeController($twig);
            $modelForge = new ForgeModel($twig);
            $registerForge = new ForgeRegister($twig);
            $validatorForge = new ForgeValidator($twig);

            CliBase::billboard('construction', 300, 'left');
            $input = $cli->bold()->white()->input('Press enter to begin. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('construction', 400, '-right');

            $cli->br();
            $cli->bold()->white()->border('*');
            $cli->bold()->white('Building project');
            $cli->bold()->white()->border('*');
            foreach ($routeList as $tableName => $route) {
                $cli->br();
                $cli->bold()->lightGreen('Working on: ' . $tableName);
                $progress = $cli->progress()->total(count(self::PROGRESS_STAGES));
                foreach (self::PROGRESS_STAGES as $key => $stage) {
                    $progress->current($key + 1, $stage);
                    switch ($stage) {
                        case 'Model':
                            $tableDetails = DatabaseUtilities::getTableDetails($tableName);
                            $pk = $tableDetails->getPrimaryKey();
                            $pkColumns = $pk ? $pk->getColumns() : [];
                            $columns = $tableDetails->getColumns();
                            $colDetails = [];
                            foreach ($columns as $column) {
                                $colArray = $column->toArray();
                                $colArray['type'] = $colArray['type']->getName();
                                $colArray['length'] ??= 'null';
                                $flags = [];
                                if (in_array($colArray['name'], $pkColumns)) {
                                    $flags[] = "PK";
                                }
                                if ($colArray['autoincrement']) {
                                    $flags[] = "AI";
                                }
                                if ($colArray['notnull']) {
                                    $flags[] = "NN";
                                }
                                if ($colArray['unsigned']) {
                                    $flags[] = "UN";
                                }
                                if ($colArray['fixed']) {
                                    $flags[] = "FX";
                                }
                                $colArray['flags'] = $flags;
                                $colDetails[] = $colArray;
                            }
                            $modelForge->forgeModel($tableName, $colDetails);
                            break;

                        case 'Controller':
                            $controllerForge->forgeController($tableName, $route);
                            break;

                        case 'Actions':
                            $actionsForge->forgeDeleteAction($tableName);
                            $actionsForge->forgeGetAction($tableName);
                            $actionsForge->forgePostAction($tableName);
                            $actionsForge->forgeRestoreAction($tableName);
                            $actionsForge->forgeSearchAction($tableName);
                            break;

                        case 'Validators':
                            $validatorForge->forgeSearchValidator($tableName);
                            $validatorForge->forgeWriteValidator($tableName);
                            $validatorForge->forgeModelValidator($tableName);
                            break;
                        default:
                            break;
                    }
                }
            }

            $cli->br();
            $cli->bold()->lightGreen('Registering controllers...');

            // Register the controllers
            $registerForge->forgeRegisterControllers();

            $cli->br();
            $cli->bold()->lightYellow()->border('*');
            $cli->bold()->lightYellow('Project build completed!');
            $cli->bold()->lightYellow()->border('*');
            $cli->br();

            // Create the .viridian semaphore file
            if (file_put_contents(self::VIRIDIAN_PATH, 'TIMESTAMP=' . time()) === false) {
                CliBase::showThrowableAndDie(new Exception('Unable to create .viridian file.'));
            }
        } catch (Throwable $throwable) {
            CliBase::showThrowableAndDie($throwable);
        }
    }

    /**
     * If a .viridian file exists then warn the user and exit
     */
    private function checkViridian() {
        try {
            // If viridian has any entries it means that make has already been run.
            // In this case the user must run the reset command before running make again.
            if (file_exists(self::VIRIDIAN_PATH)) {
                $cli = $this->cli;
                $cli->br();
                $cli->bold()
                    ->backgroundLightRed()
                    ->white()
                    ->border('*');
                $cli
                    ->bold()
                    ->backgroundLightRed()
                    ->white('                 !!!Running make is destructive!!!                              ');
                $cli
                    ->bold()
                    ->backgroundLightRed()
                    ->white(' Re-running make will destroy & replace all models, controllers, models etc.    ');
                $cli
                    ->bold()
                    ->backgroundLightRed()
                    ->white()
                    ->border(' ');
                $cli
                    ->bold()
                    ->backgroundLightRed()
                    ->white(' You must run the reset command before you can re-run the make command.         ');
                $cli->bold()
                    ->backgroundLightRed()
                    ->white()
                    ->border('*');
                $cli->br();
                $input = $cli->bold()->lightGray()->input('Press enter to exit');
                $input->prompt();
                die();
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
