<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Illuminate\Database\Connection;

trait DatabaseTrait
{
    /**
     * Given the connection object query the database to get the tables in the database as an array
     * @param Connection $conn
     * @return array
     */
    protected function getTableList(Connection $conn): array
    {
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

        return $conn->select($select);
    }
}
