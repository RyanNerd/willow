<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Illuminate\Database\Capsule\Manager;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Willow\Robo\Plugin\Commands\Traits\{ModelTrait, RegisterControllersTrait, EnvSetupTrait};

class MakeCommands extends RoboBase
{
    use EnvSetupTrait;
    use ModelTrait;
    use RegisterControllersTrait;

    protected Environment $twig;
    private const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';

    /**
     * Builds the app (routes, controllers, actions, middleware, etc.
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
                    $container->set('ENV', include __DIR__ . '/../../../../config/_env.php');
                    if (!$container->has('ENV')) {
                        die(self::ENV_ERROR);
                    } else {
                        if (!$container->has('Eloquent')) {
                            $container->set('Eloquent', include __DIR__ . '/../../../../config/db.php');
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
        $cli->shout('Database Name: ' . $conn->getDatabaseName());

        $cli->white('Trying RegisterControllers...');

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/Templates');
        $twig = new Environment($loader);

//      TODO: Create models
//      $error = $this->forgeModel('blah');
//      if ($error) {
//          die($error);
//      }

        $error = $this->forgeRegisterControllers($twig);
        if ($error) {
            die($error);
        } else {
            $cli->white('Finished RegisterControllers ');
        }

    }
}
