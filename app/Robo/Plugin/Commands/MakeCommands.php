<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as Twig;
use Throwable;
use Exception;

class MakeCommands extends CommandsBase
{
    protected const PROGRESS_STAGES = [
        'Model',
        'Controller',
        'Actions',
        'Validators'
    ];

    private const VIRIDIAN_PATH = __DIR__ . '/../../../../.viridian';
    private const DOT_ENV_PATH = __DIR__ . '/../../../../.env';
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';
    private CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();

        // Does the .env file not exist? If not then prompt user and create
        if (!file_exists(self::DOT_ENV_PATH)) {
            CliBase::billboard('make-env', 160, 'top');
            $input = $this->cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('welcome', 160, '-top');
            $this->cli->clear();
            UserReplies::setEnvFromUser();
        }
        include_once self::DOT_ENV_INCLUDE_FILE;

        // Check for a previous project build out
        $this->checkViridian();
    }

    /**
     * Builds the app using database tables
     */
    final public function makeBuild(): void {
        try {
            $cli = $this->cli;
            CliBase::billboard('make-tables', 165, 'bottom');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('make-tables', 165, '-top');
            $cli->clear();

            // Get the list of tables the user wants in their project
            $selectedTables = UserReplies::getTableSelection(DatabaseUtilities::getTableList());


            $cli = $this->cli;
            CliBase::billboard('make-routes', 165, 'top');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('make-routes', 165, '-bottom');
            $cli->clear();

            // Prompt the user for the route for each table.
            foreach ($selectedTables as $table) {
                self::showColumns($table);

                // Get the routes for each table that the user wants to use
                // FIXME: Should prompt per table
                $cli->bold()->yellow('Table: '. $table);
                $selectedRoutes = UserReplies::getRouteSelection($selectedTables);
            }

            $loader = new FilesystemLoader(__DIR__ . '/Templates');
            $twig = new Twig($loader);
            $actionsForge = new ActionsForge($twig);
            $controllerForge = new ForgeController($twig);
            $modelForge = new ForgeModel($twig);
            $registerForge = new ForgeRegister($twig);
            $validatorForge = new ForgeValidator($twig);

            $cli->br();
            $cli->bold()->white()->border('*');
            $cli->bold()->white('Building project');
            $cli->bold()->white()->border('*');
            foreach ($selectedRoutes as $tableName => $route) {
                $cli->br();
                $cli->bold()->lightGreen('Working on: ' . $tableName);
                $progress = $cli->progress()->total(count(self::PROGRESS_STAGES));
                foreach (self::PROGRESS_STAGES as $key => $stage) {
                    $progress->current($key + 1, $stage);
                    switch ($stage) {
                        case 'Model':
                            // TODO: Use DatabaseUtilities::getTableDetails see dbColumns() in DatabaseCommands.php
                            $tableDetails = DatabaseUtilities::getTableAttributes($tableName);
                            $columnList = [];
                            foreach ($tableDetails as $columnName => $type) {
                                $columnList[] = ['column_name' => $columnName, 'type' => $type];
                            }
                            $modelForge->forgeModel($tableName, $columnList);
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
                            $validatorForge->forgeRestoreValidator($tableName);
                            $validatorForge->forgeSearchValidator($tableName);
                            $validatorForge->forgeWriteValidator($tableName);
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
     * Resets the project back to factory defaults
     */
    final public function makeReset(): void {
        $cli = $this->cli;

        try {
            $container = DatabaseUtilities::getContainer();

            $viridian = $container->get('viridian');

            // If viridian has no entries then there's nothing to do.
            if (count($viridian) === 0) {
                $cli->bold()->white('Project appears to be uninitialized. Nothing to do.');
                die();
            }

            $cli->br();
            $cli->bold()
                ->backgroundLightRed()
                ->white()
                ->border('*');
            $cli
                ->bold()
                ->backgroundLightRed()
                ->white('Running reset will allow the make command to be re-run.');
            $cli
                ->bold()
                ->backgroundLightRed()
                ->white('Running make more than once is a destructive action.');
            $cli
                ->bold()
                ->backgroundLightRed()
                ->white('Any code in the controllers, models, routes, etc. will be overwritten.');
            $cli->br();
            /** @var Input $input */
            $input = $cli->bold()->lightGray()->confirm('Are you sure you want to reset?');
            if ($input->confirmed()) {
                unlink(self::VIRIDIAN_PATH);
            }
            $cli->bold()
                ->backgroundLightRed()
                ->white()
                ->border('*');
            $cli->br();
            die();
        } catch (Exception $e) {
            CliBase::showThrowableAndDie($e);
        }
    }

    /**
     * If a .viridian file exists then warn the user and exit
     */
    private function checkViridian() {
        try {
            // If viridian has any entries it means that make has already been run.
            // In this case the user must run the reset command before running make again.
            if (file_exists(__DIR__ . '/../../../../.viridian')) {
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
