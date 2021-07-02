<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Illuminate\Database\Capsule\Manager as Eloquent;

class DatabaseUtilities
{
    private static Container|null $container = null;

    /**
     * If the container hasn't been initalized then build the container and return it
     * @return Container
     * @throws \Exception
     */
    private static function getContainer(): Container {
        if (self::$container === null) {
            if (!file_exists(__DIR__ . '/../../../../.env')) {
                throw new Exception("Database configuration is not set. The .env file is missing.");
            }
            $configPath = __DIR__ . '/../../../../config/';
            $builder = (new ContainerBuilder())
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
     * Return Doctrine's AbstractSchemaManager
     * @return AbstractSchemaManager
     * @throws Exception
     */
    public static function getDbalSchema(): AbstractSchemaManager {
        return self::getEloquent()->getConnection()->getDoctrineSchemaManager();
    }

    /**
     * Given a table name return an array of indexes
     * @param string $tableName
     * @return Index[]
     * @throws DoctrineException
     */
    public static function getTableIndexes(string $tableName): array {
        return self::getDbalSchema()->listTableIndexes($tableName);
    }

    /**
     * Given a table name return a table details object
     * @param string $tableName
     * @return Table
     * @throws DoctrineException
     * @throws Exception
     */
    public static function getTableDetails(string $tableName): Table {
        return self::getDbalSchema()->listTableDetails($tableName);
    }
}
