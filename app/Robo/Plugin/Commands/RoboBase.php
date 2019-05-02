<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\CLImate\CLImate;
use Robo\Tasks;
use Throwable;

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

    /**
     * @var Container
     */
    protected $willowContainer;

    /**
     * RoboBase constructor.
     */
    public function __construct()
    {
        $this->cli = new CLImate;
        $this->cli->style->addCommand('warning', ['bold', 'white', 'blink']);
        $this->cli->style->addCommand('err', ['backgroundLightRed', 'bold', 'white', 'blink']);

        // Set up DI and ORM only if the .env file exists.
        if (file_exists(__DIR__ . '/../../../../.env')) {
            // Load Default configuration from environment
            include_once __DIR__ . '/../../../../config/_env.php';

            // Set up Dependency Injection
            try {
                $builder = new ContainerBuilder();
                $builder->addDefinitions(__DIR__ . '/../../../../config/db.php');
                $container = $builder->build();
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());
                return;
            }

            $this->willowContainer = $container;
            $this->bootDatabase($container);
        }
    }

    /**
     * Create an Eloquent ORM capsule from the .env setttings
     *
     * @param Container $container
     */
    protected function bootDatabase(Container $container)
    {

        // Establish an instance of the Illuminate database capsule (if not already established)
        try {
            if ($this->capsule === null) {
                $this->capsule = $container->get(Capsule::class);
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return;
        }
    }

    /**
     * Climate helper function
     *
     * @param string $warningMessage
     */
    protected function warning(string $warningMessage)
    {
        $this->cli->warning()->inline('WARNING: ');
        $this->cli->backgroundLightRed()->white($warningMessage);
    }

    /**
     * Climate helper function
     *
     * @param string $errorMessage
     */
    protected function error(string $errorMessage)
    {
        $this->cli->err()->inline('ERROR: ');
        $this->cli->error($errorMessage);
    }

    protected function isDatabaseEnvironmentReady(): bool
    {
        if ($this->capsule === null) {
            $this->error('Database not set up. Run willow:init or create the .env file manually.');
            return false;
        }

        return true;
    }

    protected function getTableDetails(string $tableName): array
    {
        $tableDetails = [];
        $capsule = $this->capsule;
        $schema = $capsule::schema();
        $columns = $schema->getColumnListing($tableName);
        foreach ($columns as $column) {
            $columnType = $schema->getColumnType($tableName, $column);
            $tableDetails[$column] = $columnType;
        }
        return $tableDetails;
    }
}