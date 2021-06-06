<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Illuminate\Database\Capsule\Manager as Eloquent;
use Throwable;
use Willow\Robo\Plugin\Commands\Traits\{
    EnvSetupTrait,
    ModelTrait,
    RegisterControllersTrait,
    RouteSetupTrait,
    TableSetupTrait
};

class MakeCommands extends RoboBase
{
    use EnvSetupTrait;
    use ModelTrait;
    use RegisterControllersTrait;
    use RouteSetupTrait;
    use TableSetupTrait;

    private const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';

    protected const PROGRESS_STAGES = [
        'Model',
        'Controllers',
        'Actions',
        'Validators',
        'Routes'
    ];

    /**
     * Builds the app using database tables
     */
    public function make() {
        $cli = $this->cli;
        $container = self::_getContainer();

        // If viridian has any entries it means that make has already been run.
        // In this case the use must run the reset command before running make again.
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

        try {
            // Has .env file been read into ENV? If not create the .env file and load it into the container
            if (!$container->has('ENV')) {
                // Get the .env contents from the user
                $envText = $this->envInit();

                // Was the .env file successfully created?
                if (file_put_contents(__DIR__ . '/../../../../.env', $envText) !== false) {
                    // Validate the .env file.
                    $env = include  __DIR__ . '/../../../../config/_env.php';
                    // Dynamically add ENV to the container
                    $container->set('ENV', $env['ENV']);
                } else {
                    die(self::ENV_ERROR);
                }
            }
        } catch (Throwable $throwable) {
            self::outputThrowableMessage($cli, $throwable);
            die(self::ENV_ERROR);
        }

        try {
            // Has Eloquent been defined?
            if (!$container->has('Eloquent')) {
                // Dynamically add Eloquent to the container
                $db = include (__DIR__ . '/../../../../config/db.php');
                $container->set('Eloquent', $db['Eloquent']);
            }

            /** @var Eloquent $eloquent */
            $eloquent = $container->get('Eloquent');

            // Get the database connection object
            $conn = $eloquent->getConnection();

            // Get the tables from the database
            $tables = DatabaseUtilities::getTableList($conn);
        } catch (Throwable $throwable) {
            self::outputThrowableMessage($cli, $throwable);
            die('Unable to connect to database. Check that the .env configuration is correct');
        }

        // Get the list of tables the user wants in their project
        $selectedTables = $this->tableInit($tables);

        // Get the routes for each table that the user wants to use
        $selectedRoutes = $this->routeInit($selectedTables);

        /**
         * TODO: Build out the model, controllers, actions, etc.
         */

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

                // Simulate something happening
                // TODO: Actually do something
                usleep(980000);
            }
        }

        $viridianText = '';
        foreach ($selectedRoutes as $table => $route) {
            $viridianText .= $table . '=' . $route . PHP_EOL;
        }

        // TODO: At the end of it all.
        if (file_put_contents(__DIR__ . '/../../../../.viridian', $viridianText) === false) {
            die('Unable to create .viridian config file.');
        }


        $input = $cli->input('Press enter to continue');
        $input->prompt();

//        // TODO: Models, Controllers, etc.
//        $cli->white('Trying RegisterControllers...');
//
//        // Set up Twig
//        $loader = new FilesystemLoader(__DIR__ . '/Templates');
//        $twig = new Environment($loader);
//
//        $error = $this->forgeRegisterControllers($twig);
//        if ($error) {
//            die($error);
//        } else {
//            $cli->white('Finished RegisterControllers ');
//        }
    }
}
