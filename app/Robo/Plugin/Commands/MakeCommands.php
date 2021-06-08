<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Confirm;
use Throwable;
use Willow\Robo\Plugin\Commands\Traits\{EnvSetupTrait,
    ForgeModelTrait,
    RegisterControllersTrait,
    RouteSetupTrait,
    TableSetupTrait,
};

class MakeCommands extends RoboBase
{
    use EnvSetupTrait;
    use ForgeModelTrait;
    use RegisterControllersTrait;
    use RouteSetupTrait;
    use TableSetupTrait;

    protected const PROGRESS_STAGES = [
        'Model',
        'Controller',
        'Actions',
        'Validators',
        'Routes'
    ];

    private const VIRIDIAN_PATH = __DIR__ . '/../../../../.viridian';

    /**
     * Builds the app using database tables
     */
    public function make() {
        $cli = $this->cli;
        $container = self::_getContainer();

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
            /** @var Confirm $input */
            $input = $cli->bold()->lightGray()->confirm('Do you want to OVERWRITE it?');
            $cli->bold()->yellow()->border();
            if ($input->confirmed()) {
                $this->setEnvFromUser();
            }
        } else {
            $this->setEnvFromUser();
        }

        // Get Eloquent ORM manager
        $eloquent = $this->getEloquent();

        // Get the database connection object
        $conn = $eloquent->getConnection();

        try {
            // Get the tables from the database
            $tables = DatabaseUtilities::getTableList($conn);
        } catch (Throwable $throwable) {
            $cli->br();
            $cli->bold()->yellow()->border('*');
            $cli->bold()->yellow('An error occurred attempting to connect with the database.');
            $cli->bold()->yellow('Check the .env file for misconfigurations.');
            $cli->br();
            /** @var Confirm $input */
            $input = $cli->bold()->lightGray()->confirm('Do you want to see the error details?');
            $cli->bold()->yellow()->border('*');
            if ($input->confirmed()) {
                $this->outputThrowableMessage($throwable);
            }
            die();
        }

        // Get the list of tables the user wants in their project
        $selectedTables = $this->tableInit($tables);

        // Get the routes for each table that the user wants to use
        $selectedRoutes = $this->routeInit($selectedTables);

        // Create the .viridian semaphore file
        if (file_put_contents(self::VIRIDIAN_PATH, 'TIMESTAMP=' . (string)time()) === false) {
            die('Unable to create .viridian file.');
        }

        /**
         * TODO: Build out the model, controllers, actions, etc.
         */

        $this->twig = self::_getContainer()->get('twig');

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
                    case ('Model'): {
                        $this->forgeModel($table);
                        // TODO: Model stuff
                        break;
                    }

                    case ('Controller'): {
                        // TODO: Controller stuff
                        break;
                    }

                    case ('Actions'): {
                        // TODO: Action stuff
                        break;
                    }

                    case ('Validators'): {
                        // TODO: Validator stuff
                        break;
                    }

                    case ('Routes'): {
                        // TODO: Route stuff
                    }
                }
            }
        }
    }

    /**
     * Resets the project back to factory defaults
     */
    public function reset() {
        $cli = $this->cli;
        $container = self::_getContainer();

        $viridian = $container->get('viridian');

        // If viridian has no entries then there's nothing to do.
        if (count($viridian) === 0) {
            $cli->bold()->white('Project appears to be uninitialized. Nothing to do.');
            /** @var Confirm $input */
            $input = $cli->lightGray()->confirm('Do you want to force a reset anyway?');
            if (!$input->confirmed()) {
                die();
            }
        }

        // TODO: Implement reset command
        $cli->shout('reset not implemented.');
    }
}
