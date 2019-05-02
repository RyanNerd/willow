<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

class DbCommands extends RoboBase
{
    /**
     * Show all tables in the database
     *
     * @todo Postgres "SELECT table_schema,table_name, table_catalog FROM information_schema.tables WHERE table_catalog = 'CATALOG/SCHEMA HERE' AND table_type = 'BASE TABLE' AND table_schema = 'public' ORDER BY table_name;"
     * @todo SQLite "SELECT `name` FROM sqlite_master WHERE `type`='table'  ORDER BY name";
     * @todo MSSQL "select Table_Name, table_type from information_schema.tables";
     *
     * @see https://stackoverflow.com/questions/33478988/how-to-fetch-the-tables-list-in-database-in-laravel-5-1
     * @see https://stackoverflow.com/questions/29817183/php-mssql-pdo-get-table-names
     */
    public function dbShowTables()
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $capsule = $this->capsule;
        $conn = $capsule->getConnection();
        $db = $conn->getDatabaseName();
        $select = "SELECT table_name
            FROM INFORMATION_SCHEMA.tables
            WHERE table_schema = '$db'
            ORDER BY table_name;";
        $rows = $conn->select($select);
        foreach ($rows as $row) {
            $this->cli->blue()->bold()->out($row->table_name);
        }
    }

    /**
     * Show column details for a given table
     *
     * @param string $tableName
     */
    public function dbShowColumns(string $tableName)
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $columns = $this->getTableDetails($tableName);
        foreach ($columns as $columnName => $columnType) {
            $this->cli->blue()->bold()->out($columnName . ' => ' . $columnType);
        }
    }
}