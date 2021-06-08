<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Eloquent;
use League\CLImate\CLImate;
use Robo\Tasks;
use Throwable;
use Willow\Robo\Plugin\Commands\Traits\EnvSetupTrait;

abstract class RoboBase extends Tasks
{
    protected CLImate $cli;

    /**
     * @var Container | null
     */
    protected  static $_container;

    protected const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';
    protected const CONFIG_PATH = __DIR__ . '/../../../../config/';
    protected const ENV_PATH = __DIR__ . '/../../../../.env';

    use EnvSetupTrait;

    public function __construct()
    {
        $this->cli = new CLImate();

        try {
            // Set up DI
            if (!static::$_container instanceof Container) {
                $builder = new ContainerBuilder();
                $builder = $builder->addDefinitions( self::CONFIG_PATH . '/_viridian.php');

                if (file_exists(self::ENV_PATH)) {
                    $builder = $builder
                    ->addDefinitions(self::CONFIG_PATH . '_env.php')
                    ->addDefinitions(self::CONFIG_PATH . 'db.php')
                    ->addDefinitions(self::CONFIG_PATH . 'twig.php');
                }
                $container = $builder->build();
                self::_setContainer($container);
            }
        } catch (Throwable $throwable) {
            $cli = $this->cli;
            $cli->br(2);
            $cli->bold()->yellow('[WARNING] Something went wrong');
            $cli->bold()->white('Check that the .env file is valid');
            $cli->bold()->yellow()->inline('Error Message: ')->white($throwable->getMessage());
            $cli->br(2);
            exit();
        }
    }

    /**
     * Set the DI/Container
     * @param Container $container
     */
    public static function _setContainer(Container $container) {
        static::$_container = $container;
    }

    /**
     * Return the DI/Container
     * @return Container
     */
    public static function _getContainer(): Container
    {
        return static::$_container;
    }

    /**
     * Climate helper function
     * @param string $warningMessage
     */
    protected function warning(string $warningMessage): void
    {
        $this->cli->bold()->yellow()->inline('[WARNING] ');
        $this->cli->yellow($warningMessage);
    }

    /**
     * Climate helper function
     * @param string $errorMessage
     */
    protected function error(string $errorMessage): void
    {
        $this->cli->bold()->red()->inline('[ERROR] ');
        $this->cli->lightRed($errorMessage);
    }

    /**
     * Show error details
     * @param Throwable $throwable
     */
    protected function outputThrowableMessage(Throwable $throwable) {
        $cli = $this->cli;
        $cli->br();
        $cli->error('Error: ' . $throwable->getMessage());
        $cli->bold()->red()->json([self::parseThrowableToArray($throwable)]);
        $cli->br();
    }

    /**
     * Given a Throwable object parse the properties and return the result as [['label' => 'value],...]
     * @param Throwable $t
     * @return array[]
     */
    public static function parseThrowableToArray(Throwable $t): array
    {
        $traceString = $t->getTraceAsString();
        $tracer = explode("\n", $traceString);
        $contents = [
            'Message' => $t->getMessage(),
            'File' => $t->getFile(),
            'Line' => (string)$t->getLine()
        ];

        foreach ($tracer as $item=>$value) {
            $contents['Trace' . $item] = $value;
        }

        return $contents;
    }

    /**
     * Get the .env settings from the user
     * Save them in the .env file.
     * Validate and set the 'ENV' entry in the container.
     * @throws {self::ENV_ERROR}
     */
    protected function setEnvFromUser() {
        try {
            // Get the .env contents from the user
            $envText = $this->envInit();
            // Was the .env file successfully created?
            if (file_put_contents(self::ENV_PATH, $envText) !== false) {
                // Validate the .env file.
                $env = include __DIR__ .  '/../../../../config/_env.php';
                // Dynamically add ENV to the container
                self::_getContainer()->set('ENV', $env['ENV']);
            } else {
                die(self::ENV_ERROR);
            }
        } catch (Throwable $throwable) {
            $this->outputThrowableMessage($throwable);
            die(self::ENV_ERROR);
        }
    }

    /**
     * Checks the container to see if Eloquent has already been defined.
     * If not dynamically add Eloquent to the container;
     * @return Eloquent
     */
    protected function getEloquent(): Eloquent {
        try {
            // Has Eloquent been defined?
            if (!self::_getContainer()->has('Eloquent')) {
                // Dynamically add Eloquent to the container
                $db = include __DIR__ . '/../../../../config/db.php';
                self::_getContainer()->set('Eloquent', $db['Eloquent']);
            }
            return self::_getContainer()->get('Eloquent');
        } catch (Throwable $throwable) {
            $this->outputThrowableMessage($throwable);
            die('Unable to connect to database. Check that the .env configuration is correct');
        }
    }
}
