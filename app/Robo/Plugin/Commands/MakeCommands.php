<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Illuminate\Database\Capsule\Manager as Eloquent;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Willow\Robo\Plugin\Commands\Traits\{EnvSetupTrait, ModelTrait, RegisterControllersTrait, TableSetupTrait};

class MakeCommands extends RoboBase
{
    use EnvSetupTrait;
    use ModelTrait;
    use RegisterControllersTrait;
    use TableSetupTrait;

    private const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';

    /**
     * Builds the app using the tables in the DB to create routes, controllers, actions, middleware, etc.
     */
    public function make() {
        $cli = $this->cli;
        $container = self::_getContainer();

        $viridian = $container->get('viridian');
        if (count($viridian) > 0) {
            $this->warning('Running make is destructive!');
            $this->warning('Rerunning it will destroy and replace all Controllers, actions, etc.');
            $input = $cli->input('Proceed?');
            $input->defaultTo('No');
            $input->accept(['Yes', 'No']);
            $response = $input->prompt();
            if ($response === 'No') {
                return;
            }
        }

        // Create the .env file if it doesn't exist.
        try {
            if (!$container->has('ENV')) {
                if ($this->envInit(__DIR__ . '/../../../../.env')) {
                    // Validate the .env file.
                    $env = include  __DIR__ . '/../../../../config/_env.php';
                    // Dynamically add ENV to the container
                    $container->set('ENV', $env['ENV']);
                    // Sanity check
                    if (!$container->has('ENV')) {
                        die(self::ENV_ERROR);
                    }
                } else {
                    die(self::ENV_ERROR);
                }
            }
        } catch (Throwable $throwable) {
            self::outputThrowableMessage($cli, $throwable);
            die(self::ENV_ERROR);
        }

        $cli->br();
        $cli->bold()->white('Connecting to the database and getting list of tables');
        try {
            // Has Eloquent been defined?
            if (!$container->has('Eloquent')) {
                // Dynamically add Eloquent to the container
                $db = include (__DIR__ . '/../../../../config/db.php');
                $container->set('Eloquent', $db['Eloquent']);
            }

            /** @var Eloquent $eloquent */
            $eloquent = $container->get('Eloquent');
            $conn = $eloquent->getConnection();

            $selectedTables = $this->tableInit($conn);

            // TODO: Table routes setup
            $tableRouteList = [];
            foreach ($selectedTables as $table) {
                $tableRouteList[] = ['Table' => $table, 'Route' => strtolower($table)];
            }

            $cli->br();
            $cli->bold()->blue()->table($tableRouteList);
            $cli->br();

            $input = $cli->input('Press enter to continue');
            $input->prompt();
        } catch (Throwable $throwable) {
            self::outputThrowableMessage($cli, $throwable);
            die('Unable to connect to database. Check that the .env configuration is correct');
        }

        // TODO: Models, Controllers, etc.
        $cli->white('Trying RegisterControllers...');

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/Templates');
        $twig = new Environment($loader);

        $error = $this->forgeRegisterControllers($twig);
        if ($error) {
            die($error);
        } else {
            $cli->white('Finished RegisterControllers ');
        }
    }
}
