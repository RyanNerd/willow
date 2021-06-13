<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Eloquent;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Confirm;
use Robo\Tasks;
use Throwable;
use Willow\Robo\Plugin\Commands\Traits\EnvSetupTrait;

abstract class RoboBase extends Tasks
{
    protected CLImate $cli;

    /**
     * @var Container | null
     */
    protected static $willowContainer;

    private const ENV_ERROR = 'Unable to create the .env file. You may need to create this manually.';
    private const CONFIG_PATH = __DIR__ . '/../../../../config/';
    private const ENV_PATH = __DIR__ . '/../../../../.env';
    private const TEMPLATE_PATH = __DIR__ . '/Templates';
    private const MODELS_PATH = __DIR__ . '/../../../Robo/../Models/';
    private const CONTROLLERS_PATH = __DIR__ . '/../../../Robo/../Controllers/';

    use EnvSetupTrait;

    public function __construct() {
        $this->cli = new CLImate();

        try {
            // Set up DI
            if (!static::$willowContainer instanceof Container) {
                $builder = new ContainerBuilder();
                $builder = $builder
                    ->addDefinitions([
                        'template_path' => self::TEMPLATE_PATH,
                        'controllers_path' => self::CONTROLLERS_PATH,
                        'models_path' => self::MODELS_PATH
                    ])
                    ->addDefinitions(self::CONFIG_PATH . '/_viridian.php');
                if (file_exists(self::ENV_PATH)) {
                    $builder = $builder
                    ->addDefinitions(self::CONFIG_PATH . '_env.php')
                    ->addDefinitions(self::CONFIG_PATH . 'db.php');
                }
                $container = $builder->build();
                self::setWillowContainer($container);
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
    public static function setWillowContainer(Container $container): void {
        static::$willowContainer = $container;
    }

    /**
     * Return the DI/Container
     * @return Container
     */
    public static function getWillowContainer(): Container {
        return static::$willowContainer;
    }

    /**
     * Display an optional error message and prompt the user if they want to see the details of the error then die.
     * @param Throwable $throwable
     * @param array|null $message
     */
    public static function showThrowableAndDie(Throwable $throwable, ?array $message = null): void { // phpcs:ignore
        $cli = new CLImate();
        $cli->br();
        $cli->bold()->yellow()->border('*');
        if ($message !== null) {
            foreach ($message as $text) {
                $cli->bold()->yellow($text);
            }
        } else {
            $cli->bold()->yellow('An error has occurred.');
        }
        $cli->br();
        /** @var Confirm $input */
        $input = $cli->bold()->lightGray()->confirm('Do you want to see the error details?');
        $cli->bold()->yellow()->border('*');
        if ($input->confirmed()) {
            $cli->br();
            $cli->bold()->red()->json([self::parseThrowableToArray($throwable)]);
            $cli->br();
        }
        die();
    }

    /**
     * Given a Throwable object parse the properties and return the result as [['label' => 'value],...]
     * @param Throwable $t
     * @return array[]
     */
    public static function parseThrowableToArray(Throwable $t): array {
        $traceString = $t->getTraceAsString();
        $tracer = explode("\n", $traceString);
        $contents = [
            'Message' => $t->getMessage(),
            'File' => $t->getFile(),
            'Line' => (string)$t->getLine()
        ];
        foreach ($tracer as $key => $value) {
            $contents['Trace' . $key] = $value;
        }
        return $contents;
    }

    /**
     * Get the .env settings from the user
     * Save them in the .env file.
     * Validate and set the 'ENV' entry in the container.
     * @throws {self::ENV_ERROR}
     */
    final protected function setEnvFromUser(): void {
        try {
            // Get the .env contents from the user
            $envText = $this->envInit();
            // Was the .env file successfully created?
            if (file_put_contents(self::ENV_PATH, $envText) !== false) {
                // Validate the .env file.
                $env = include __DIR__ .  '/../../../../config/_env.php';
                // Dynamically add ENV to the container
                self::getWillowContainer()->set('ENV', $env['ENV']);
            } else {
                die(self::ENV_ERROR);
            }
        } catch (Throwable $throwable) {
            self::showThrowableAndDie($throwable, [self::ENV_ERROR]);
        }
    }

    /**
     * Checks the container to see if Eloquent has already been defined.
     * If not dynamically add Eloquent to the container;
     * @return Eloquent
     */
    public static function getEloquent(): Eloquent {
        try {
            // Has Eloquent been defined?
            if (!self::getWillowContainer()->has('Eloquent')) {
                // Dynamically add Eloquent to the container
                $db = include __DIR__ . '/../../../../config/db.php';
                self::getWillowContainer()->set('Eloquent', $db['Eloquent']);
            }
            return self::getWillowContainer()->get('Eloquent');
        } catch (Throwable $throwable) {
            self::showThrowableAndDie(
                $throwable,
                ['Unable to connect to database. Check that the .env configuration is correct']
            );
        }
        die();
    }
}
