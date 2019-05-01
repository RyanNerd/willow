<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

class DbCommands extends RoboBase
{
    /**
     * Show all tables in the database
     *
     * @todo Postgres "SELECT table_schema,table_name, table_catalog FROM information_schema.tables WHERE table_catalog = 'CATALOG/SCHEMA HERE' AND table_type = 'BASE TABLE' AND table_schema = 'public' ORDER BY table_name;"
     * @todo MSSQL, SQLite, etc.
     * @see https://stackoverflow.com/questions/33478988/how-to-fetch-the-tables-list-in-database-in-laravel-5-1
     */
    public function dbShowTables()
    {
        if (!$this->isDatabaseEnvironmentReady()) return;

        $capsule = $this->capsule;
        $conn = $capsule->getConnection();
        $driver = $conn->getDriverName();
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

        $capsule = $this->capsule;
        $schema = $capsule::schema();
        $columns = $schema->getColumnListing($tableName);
        foreach ($columns as $column) {
            $columnType = $schema->getColumnType($tableName, $column);
            $this->cli->blue()->bold()->out($column . ' ' . $columnType);
        }
    }

    private function isDatabaseEnvironmentReady(): bool
    {
        if ($this->capsule === null) {
            $this->error('Database not set up. Run init or edit the .env file directly.');
            return false;
        }

        return true;
    }
}