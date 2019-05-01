<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;


use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\CLImate\CLImate;
use Robo\Tasks;

abstract class RoboBase extends Tasks
{
    /**
     * @var CLImate
     */
    protected $cli;

    /**
     * @var Capsule
     */
    protected $capsule = null;

    public function __construct()
    {
        $this->cli = new CLImate;
        $this->cli->style->addCommand('warning', ['bold', 'white', 'blink']);
        $this->cli->style->addCommand('err', ['backgroundLightRed', 'bold', 'white', 'blink']);
    }

    protected function bootDatabase()
    {
        // Set up DI and ORM only if the .env file exists.
        if (file_exists(__DIR__ . '/.env')) {
            // Load Default configuration from environment
            include_once __DIR__ . '/config/_env.php';

            // Set up Dependency Injection
            try {
                $builder = new ContainerBuilder();
                $builder->addDefinitions(__DIR__ . '/config/db.php');
                $container = $builder->build();
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());
                return;
            }

            // Establish an instance of the Illuminate database capsule (if not already established)
            try {
                if ($this->capsule === null) {
                    $this->capsule = $container->get(Capsule::class);
                }
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());
                return;
            }
        }
    }

    protected function warning(string $warningMessage)
    {
        $this->cli->warning()->inline('WARNING: ');
        $this->cli->backgroundLightRed()->white($warningMessage);
    }

    protected function error(string $errorMessage)
    {
        $this->cli->err()->inline('ERROR: ');
        $this->cli->error($errorMessage);
    }
}