<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\CLImate;

class DatabaseCommands extends CommandsBase
{
    private CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();
    }

    /**
     * Show all the tables
     */
    final public function dbTables(): void {
        $this->checkEnvLoaded();
        $tables = DatabaseUtilities::getTableList();
        $tableList = [];
        foreach ($tables as $table) {
            $tableList[] = ['table_name' => $table];
        }

        // Display the list of tables in a grid
        $cli = $this->cli;
        $cli->br();
        $cli->bold()->blue()->table($tableList);
        $cli->br();
    }

    /**
     * Show all the indexes for a selected table
     * @throws Exception
     */
    final public function dbIndexes() {
        $this->checkEnvLoaded();
        $dbIndexes = DatabaseUtilities::getTableIndexes(self::getSingleTableSelection());
        if ($dbIndexes) {
            $columnIndexes = [];
            foreach ($dbIndexes as $index) {
                $indexName = $index->getName();
                $columnName = implode(", \n", $index->getUnquotedColumns());

                $flags = [];
                $flags[] = $index->isPrimary() ? '[PK]' : '[  ]';
                $flags[] = $index->isUnique() ? '[UQ]' : '[  ]';
                $flags[] = $index->isSimpleIndex() ? '[IX]' : '[  ]';

                $columnIndexes[] = [
                'Column' => $columnName,
                'Index Name' => $indexName,
                'Flags' => implode('', $flags)
                ];
            }

            $cli = CliBase::getCli();
            $cli->bold()->blue()->table($columnIndexes);
        } else {
            CliBase::getCli()->red()->backgroundLightGray('Unable to load table details');
        }
    }

    /**
     * Show column details for a selected table
     */
    final public function dbColumns() {
        $this->checkEnvLoaded();
        $tabDetails = DatabaseUtilities::getTableDetails(self::getSingleTableSelection());
        $pk = $tabDetails->getPrimaryKey();
        $pkColumns = $pk ? $pk->getColumns() : [];
        $columns = $tabDetails->getColumns();
        $colDetails = [];
        foreach ($columns as $column) {
            $colArray = $column->toArray();
            $colDetails[] = [
                '<bold><white>Name' => '<bold><white>' . $colArray['name'],
                '<bold><white>Type' => '<bold><blue>' . $colArray['type']->getName(),
                '<bold><white>Len' => '<bold><blue>' . $colArray['length'],
                '<bold><white>PK' => '<bold><blue>' . in_array($colArray['name'], $pkColumns) ? 'X':' ',
                '<bold><white>NN' => '<bold><blue>' . $colArray['notnull'] ? 'X':' ',
                '<bold><white>AI' => '<bold><blue>' . $colArray['autoincrement'] ? 'X':' ',
                '<bold><white>UN' => '<bold><blue>' . $colArray['unsigned'] ? 'X':' ',
                '<bold><white>Dft' => '<bold><blue>' . $colArray['default'],
                '<bold><white>Cmnt' => '<bold><blue>' . chunk_split($colArray['comment'] ?? '', 15)
            ];
        }
        CliBase::getCli()->table($colDetails);
    }

    /**
     * Show details for a selected table
     * @throws Exception
     */
    final public function dbDetails() {

        $this->checkEnvLoaded();
        $tableDetails = DatabaseUtilities::getTableDetails(self::getSingleTableSelection());
        $options = $tableDetails->getOptions();
        $fk = $tableDetails->getForeignKeys();
        $pk = $tableDetails->getPrimaryKey();
        if ($pk) {
            $pkName = $pk->getName();
            $pkColumns = implode(',', $pk->getColumns());
        } else {
            $pkName = 'No PK';
            $pkColumns = 'null';
        }

        $showDetails = [
            [
                'Name' => 'Primary Key',
                'Setting' => $pkName
            ],
            [
                'Name' => 'PK Column(s)',
                'Setting' => $pkColumns
            ]
        ];

        foreach ($options as $key => $value) {
            if (is_string($value) || is_int($value)) {
                $showDetails[] = ['Name' => $key, 'Setting' => $value];
            }
        }

        foreach ($fk as $key => $value) {
            $showDetails[] = [
                'Name' => 'Local (' . $value->getName() . ')',
                'Setting' => implode(',', $value->getLocalColumns())
            ];

            $showDetails[] = ['Name' => 'Local Table for: ' . $key, 'Setting' => $value->getLocalTableName()];
            $showDetails[] = ['Name' => 'Foreign Table', 'Setting' => $value->getForeignTableName()];
            $showDetails[] = ['Name' => 'Foreign Column(s)', 'Setting' => implode(',', $value->getForeignColumns())];
        }
        CliBase::getCli()->br()->table($showDetails);
    }
}
