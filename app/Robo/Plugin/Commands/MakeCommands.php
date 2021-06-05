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
                    }
                } else {
                    die(self::ENV_ERROR);
                }
            }
        } catch (Throwable $throwable) {
            $cli->br();
            $cli->error('Error: ' . $throwable->getMessage());
            $cli->bold()->red()->json([$this->parseThrowableToArray($throwable)]);
            $cli->br();
            die(self::ENV_ERROR);
        }

        $cli->br();
        $cli->bold()->white('Attempting to connect to the database');
        try {
            // Has Eloquent been defined?
            if (!$container->has('Eloquent')) {
                // Dynamically add Eloquent to the container
                $db = include (__DIR__ . '/../../../../config/db.php');
                $container->set('Eloquent', $db['Eloquent']);
            }

            /** @var Manager $eloquent */
            $eloquent = $container->get('Eloquent');
            $conn = $eloquent->getConnection();
            $rows = $this->getTableList($conn);

            $cli->br();
            $cli->bold()->blue()->table($rows);
            $cli->br();

            $input = $cli->input('Press enter to continue');
            $input->prompt();
        } catch (Throwable $throwable) {
            $cli->br();
            $cli->error('Error: ' . $throwable->getMessage());
            $cli->bold()->red()->json([$this->parseThrowableToArray($throwable)]);
            $cli->br();
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

    /**
     * Given a Throwable object parse the properties and return the result as [['label' => 'value],...]
     * @param Throwable $t
     * @return array[]
     */
    protected function parseThrowableToArray(Throwable $t): array
    {
        $traceString = $t->getTraceAsString();
        $tracer = explode("\n", $traceString);
        $contents =             [
            'Message' => $t->getMessage(),
            'File' => $t->getFile(),
            'Line' => (string)$t->getLine()
        ];

        foreach ($tracer as $item=>$value) {
            $contents['Trace' . (string)$item] = $value;
        }

        return $contents;
    }
}
