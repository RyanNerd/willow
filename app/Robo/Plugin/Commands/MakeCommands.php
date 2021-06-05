<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Illuminate\Database\Capsule\Manager;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Willow\Robo\Plugin\Commands\Traits\{DatabaseTrait, EnvSetupTrait, ModelTrait, RegisterControllersTrait};

class MakeCommands extends RoboBase
{
    use DatabaseTrait;
    use EnvSetupTrait;
    use ModelTrait;
    use RegisterControllersTrait;

    private const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';

    /**
     * Builds the app using the tables in the DB to create routes, controllers, actions, middleware, etc.
     */
    public function make()
    {
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
                    } else {
                        // Has Eloquent been defined?
                        if (!$container->has('Eloquent')) {
                            // Dynamically add Eloquent to the container
                            $db = include (__DIR__ . '/../../../../config/db.php');
                            $container->set('Eloquent', $db['Eloquent']);
                        }
                    }
                } else {
                    die(self::ENV_ERROR);
                }
            }
        } catch (Throwable $throwable) {
            $cli->error($throwable->getMessage());
            die(self::ENV_ERROR);
        }

        /** @var Manager $eloquent */
        $eloquent = $container->get('Eloquent');
        $conn = $eloquent->getConnection();
        $rows = $this->getTableList($conn);

        $cli->br(2);
        $cli->lightGreen()->border('*', 80);
        foreach ($rows as $row) {
            $cli->bold()->blue($row->table_name);
        }
        $cli->lightGreen()->border('*', 80);
        $cli->br();

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
