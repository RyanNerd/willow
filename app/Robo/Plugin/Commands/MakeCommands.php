<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as Twig;
use Throwable;
use Willow\Robo\Plugin\Commands\Traits\EnvSetupTrait;
use Willow\Robo\Plugin\Commands\Traits\RouteSetupTrait;
use Willow\Robo\Plugin\Commands\Traits\TableSetupTrait;
use Exception;

class MakeCommands extends RoboBase
{
    use EnvSetupTrait;
    use RouteSetupTrait;
    use TableSetupTrait;

    protected const PROGRESS_STAGES = [
        'Model',
        'Controller',
        'Actions',
        'Validators'
    ];

    private const VIRIDIAN_PATH = __DIR__ . '/../../../../.viridian';

    /**
     * Builds the app using database tables
     */
    final public function make(): void {
        $cli = $this->cli;

        $container = self::getWillowContainer();

        try {
            // If viridian has any entries it means that make has already been run.
            // In this case the user must run the reset command before running make again.
            $viridian = $container->get('viridian');
            if (count($viridian) > 0) {
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


            // Has .env file been read into ENV?
            if ($container->has('ENV') && !empty($container->get('ENV')['DB_NAME'])) {
                $cli->bold()->yellow()->border();
                $cli->bold()->white("Database configuration already exists in .env");
                $cli->br();
                /** @var Input $input */
                $input = $cli->bold()->lightGray()->confirm('Do you want to OVERWRITE it?');
                $cli->bold()->yellow()->border();
                if ($input->confirmed()) {
                    $this->setEnvFromUser();
                }
            } else {
                $this->setEnvFromUser();
            }
        } catch (Throwable $e) {
                RoboBase::showThrowableAndDie($e);
        }

        try {
            // Get Eloquent ORM manager
            $eloquent = RoboBase::getEloquent();

            // Get the database connection object
            $conn = $eloquent->getConnection();

            // Get the tables from the database
            $tables = DatabaseUtilities::getTableList($conn);

            // Get the list of tables the user wants in their project
            $selectedTables = $this->tableInit($tables);

            // Get the routes for each table that the user wants to use
            $selectedRoutes = $this->routeInit($selectedTables);

            // Create the .viridian semaphore file
            if (file_put_contents(self::VIRIDIAN_PATH, 'TIMESTAMP=' . time()) === false) {
                RoboBase::showThrowableAndDie(new Exception('Unable to create .viridian file.'));
            }
            $loader = new FilesystemLoader(__DIR__ . '/Templates');
            $twig = new Twig($loader);
            $actionsForge = new ActionsForge($twig);
            $controllerForge = new ControllerForge($twig);
            $modelForge = new ModelForge($twig);
            $registerForge = new RegisterForge($twig);
            $validatorForge = new ValidatorForge($twig);

            $cli->br();
            $cli->bold()->white()->border('*');
            $cli->bold()->white('Building project');
            $cli->bold()->white()->border('*');
            foreach ($selectedRoutes as $table => $route) {
                $cli->br();
                $cli->bold()->lightGreen('Working on: ' . $table);
                $progress = $cli->progress()->total(count(self::PROGRESS_STAGES));
                foreach (self::PROGRESS_STAGES as $key => $stage) {
                    $progress->current($key + 1, $stage);
                    switch ($stage) {
                        case 'Model':
                            $modelForge->forgeModel($table);
                            break;

                        case 'Controller':
                            $controllerForge->forgeController($table, $route);
                            break;

                        case 'Actions':
                            $actionsForge->forgeDeleteAction($table);
                            $actionsForge->forgeGetAction($table);
                            $actionsForge->forgePatchAction($table);
                            $actionsForge->forgePostAction($table);
                            $actionsForge->forgeRestoreAction($table);
                            $actionsForge->forgeSearchAction($table);
                            break;

                        case 'Validators':
                            $validatorForge->forgeRestoreValidator($table);
                            $validatorForge->forgeSearchValidator($table);
                            $validatorForge->forgeWriteValidator($table);
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
        } catch (Throwable $throwable) {
            self::showThrowableAndDie($throwable);
        }
    }

    /**
     * Resets the project back to factory defaults
     */
    final public function reset(): void {
        $cli = $this->cli;

        try {
            $container = self::getWillowContainer();

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
            RoboBase::showThrowableAndDie($e);
        }
    }
}
