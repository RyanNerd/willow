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

            // Save container for reference.
            $this->willowContainer = $container;

            // Boot Eloquent
            $this->bootDatabase($container);
        }
    }

    /**
     * Create an Eloquent ORM capsule from the .env settings
     *
     * @param Container $container
     */
    protected function bootDatabase(Container $container): void
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
    protected function warning(string $warningMessage): void
    {
        $this->cli->bold()->yellow()->inline('[WARNING] ');
        $this->cli->yellow($warningMessage);
    }

    /**
     * Climate helper function
     *
     * @param string $errorMessage
     */
    protected function error(string $errorMessage): void
    {
        $this->cli->bold()->red()->inline('[ERROR] ');
        $this->cli->lightRed($errorMessage);
    }

    /**
     * Returns true if eloquent has booted and a connection is established to the database.
     *
     * @return bool
     */
    protected function isDatabaseEnvironmentReady(): bool
    {
        if ($this->capsule === null) {
            $this->error('Database not set up. Run willow:init or create the .env file manually.');
            return false;
        }

        try {
            $conn = $this->capsule->getConnection();
            $driver = $conn->getDriverName();

            switch ($driver) {
                case 'sqlite':
                    $sql = 'SELECT name as table_name FROM sqlite_master';
                    break;

                default:
                    $sql = 'SELECT table_name as table_name FROM INFORMATION_SCHEMA.TABLES';
            }

            $conn->select($sql . ' WHERE 1=0');
        } catch (Throwable $exception) {
            $this->error('Cannot connect to database: ' . $exception->getMessage());
        }

        return true;
    }

    /**
     * Returns an array of all the tables in the database
     *
     * @return string[]
     */
    protected function getTables(): array
    {
        $capsule = $this->capsule;
        $conn = $capsule->getConnection();
        $driver = $conn->getDriverName();
        $db = $conn->getDatabaseName();

        switch ($driver) {
            case 'sqlite':
                $select = 'SELECT name as table_name 
                    FROM sqlite_master
                    ORDER BY table_name';
                break;

            default:
                $select = "SELECT table_name as table_name
            FROM INFORMATION_SCHEMA.TABLES
            WHERE table_schema = '$db'
            ORDER BY table_name;";
        }


        $rows = $conn->select($select);
        $tables = [];
        foreach($rows as $row) {
            $tables[] = $row->table_name;
        }
        return $tables;
    }

    /**
     * Returns an array of all the views in the database
     *
     * @return string[]
     */
    protected function getViews(): array
    {
        $capsule = $this->capsule;
        $conn = $capsule->getConnection();
        $driver = $conn->getDriverName();
        $db = $conn->getDatabaseName();

        switch ($driver) {
            case 'sqlite':
                return [];

            default:
                $select = "SELECT table_name as table_name
            FROM INFORMATION_SCHEMA.VIEWS
            WHERE table_schema = '$db'
            ORDER BY table_name;";
                $rows = $conn->select($select);
                $views = [];
                foreach($rows as $row) {
                    $views[] = $row->table_name;
                }
                return $views;
        }
    }

    /**
     * Returns an associative array of column names and column types for the given tableName
     *   ex: 'id' => 'integer', 'first_name' => 'string'
     *
     * @param string $tableName
     * @return array
     */
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

    /**
     * Returns true if the current O/S is any flavor Windows
     *
     * @return bool
     */
    public static function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}