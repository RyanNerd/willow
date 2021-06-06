<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use League\CLImate\CLImate;
use Robo\Tasks;
use Throwable;

abstract class RoboBase extends Tasks
{
    protected CLImate $cli;

    /**
     * @var Container | null
     */
    protected  static $_container;

    public function __construct()
    {
        $this->cli = new CLImate();

        try {
            // Set up DI
            if (!static::$_container instanceof Container) {
                $builder = new ContainerBuilder();
                $builder = $builder->addDefinitions( __DIR__ . '/../../../../config/_viridian.php');

                if (file_exists(__DIR__ . '/../../../../.env')) {
                    $builder = $builder
                    ->addDefinitions(__DIR__ . '/../../../../config/_env.php')
                    ->addDefinitions(__DIR__ . '/../../../../config/db.php');
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

    public static function _setContainer(Container $container) {
        static::$_container = $container;
    }

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
     * Returns true if eloquent has booted and a connection is established to the database.
     * @param bool $verbose
     * @return bool
     */
    protected function isDatabaseEnvironmentReady(bool $verbose = true): bool
    {
        $container = $this->_getContainer();

        if (!$container->has('Eloquent')) {
            if ($verbose) {
                $this->error('Database not set up. Run db:init or create the .env file manually.');
            }
            return false;
        }

        try {
            /** @var Manager $eloquent */
            $eloquent = $container->get('Eloquent');
            $conn = $eloquent->getConnection();
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
            if ($verbose) {
                $this->error('Cannot connect to database: ' . $exception->getMessage());
            }
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
        $capsule = $this->_getContainer()->get('Eloquent');
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
        $capsule = $this->_getContainer()->get('Eloquent');
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
        $capsule = $this->_getContainer()->get('Eloquent');
        $schema = $capsule::schema();
        $columns = $schema->getColumnListing($tableName);
        foreach ($columns as $column) {
            $columnType = $schema->getColumnType($tableName, $column);
            $tableDetails[$column] = $columnType;
        }
        return $tableDetails;
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
