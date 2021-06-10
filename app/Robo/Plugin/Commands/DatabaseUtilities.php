<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager as Eloquent;

class DatabaseUtilities
{
    /**
     * Given the connection object query the database to get the tables in the database as an array
     * @param Connection $conn
     * @return array
     */
    public static function getTableList(Connection $conn): array {
        $driver = $conn->getDriverName();

        switch ($driver) {
            case 'sqlite':
                $select = /** @lang SQLite */
                    <<<sql
                    SELECT name as table_name
                    FROM sqlite_master
                    ORDER BY table_name
sql;
                break;

            default:
                $db = $conn->getDatabaseName();
                $select = <<<sql
                    SELECT TABLE_NAME as table_name,
                           TABLE_ROWS as row_count,
                           CREATE_TIME as created,
                           TABLE_COMMENT as comment
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE table_schema = '$db'
                    ORDER BY table_name;
sql;
        }
        return $conn->select($select);
    }

    /**
     * Returns an associative array of column names and column types for the given tableName
     * ex: ['id' => 'integer', 'first_name' => 'string']
     * @param Eloquent $eloquent
     * @param string $tableName
     * @return array<'column_name'=>'type'>
     */
    public static function getTableAttributes(Eloquent $eloquent, string $tableName): array {
        $tableDetails = [];
        $schema = $eloquent::schema();
        $columns = $schema->getColumnListing($tableName);
        foreach ($columns as $column) {
            $columnType = $schema->getColumnType($tableName, $column);
            $tableDetails[$column] = $columnType;
        }
        return $tableDetails;
    }
}
