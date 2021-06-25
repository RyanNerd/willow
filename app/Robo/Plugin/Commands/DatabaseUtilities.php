<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use Illuminate\Database\Capsule\Manager as Eloquent;

class DatabaseUtilities
{
    private static Container|null $container = null;

    /**
     * @return Container
     * @throws \Exception
     */
    public static function getContainer(): Container {
        if (self::$container === null) {
            if (!file_exists(__DIR__ . '/../../../../.env')) {
                throw new Exception("Database configuration is not set. The .env file is missing.");
            }
            $configPath = __DIR__ . '/../../../../config/';
            $builder = (new ContainerBuilder())
                ->addDefinitions($configPath . '_viridian.php')
                ->addDefinitions($configPath . '_env.php')
                ->addDefinitions($configPath . 'db.php');
            self::$container = $builder->build();
        }
        return self::$container;
    }

    /**
     * Return the Eloquent object
     * @return Eloquent
     * @throws \Exception
     */
    public static function getEloquent(): Eloquent {
        return self::getContainer()->get('Eloquent');
    }

    /**
     * Return the tables in the database as a simple array
     * @return array<string>
     * @throws DoctrineException
     * @throws Exception
     */
    public static function getTableList(): array {
        return self::getEloquent()->getConnection()->getDoctrineSchemaManager()->listTableNames();
    }

    /**
     * Returns an associative array of column names and column types for the given tableName
     * ex: ['id' => 'integer', 'first_name' => 'string']
     * @param string $tableName
     * @return array<'column_name'=>'type'>
     * @throws Exception
     */
    public static function getTableAttributes(string $tableName): array {
        $tableDetails = [];
        $schema = self::getEloquent()::schema();
        $columns = $schema->getColumnListing($tableName);
        foreach ($columns as $column) {
            $columnType = $schema->getColumnType($tableName, $column);
            $tableDetails[$column] = $columnType;
        }
        return $tableDetails;
    }

    public static function getDbalSchema() {
        return self::getEloquent()->getConnection()->getDoctrineSchemaManager();
    }

    public static function getTableIndexes(string $tableName) {
        return self::getDbalSchema()->listTableIndexes($tableName);
    }

    public static function getTableDetails(string $tableName) {
        return self::getDbalSchema()->listTableDetails($tableName);
    }
}
