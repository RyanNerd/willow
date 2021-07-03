<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\CLImate;

class DatabaseCommand extends CommandBase
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
            $tableList[] = ['<bold><white>Table Name' => $table];
        }

        // Display the list of tables in a grid
        $cli = $this->cli;
        $cli->br();
        $cli->tbl()->table($tableList);
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
                '<white>Column' => $columnName,
                '<white>Index Name' => $indexName,
                '<white>Flags' => implode('', $flags)
                ];
            }

            $cli = CliBase::getCli();
            $cli->tbl()->table($columnIndexes);
        } else {
            CliBase::getCli()->red()->backgroundLightGray('Unable to load table indexes');
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
                '<bold><white>Name</white></bold>' =>
                    '<bold><white>' . $colArray['name'] . '</bold></white>',
                '<bold><white>Type</white></bold>' =>
                    '<bold><blue>' . $colArray['type']->getName() . '</bold></blue>',
                '<bold><white>Len</white></bold>' =>
                    '<bold><blue>' . $colArray['length'] . '</bold></blue>',
                '<bold><white>PK</white></bold>' =>
                    '<bold><blue>' . in_array($colArray['name'], $pkColumns) ? 'X</bold></blue>' : ' </bold></blue>',
                '<bold><white>NN</white></bold>' =>
                    '<bold><blue>' . $colArray['notnull'] ? 'X</bold></blue>' : ' </bold></blue>',
                '<bold><white>AI</white></bold>' =>
                    '<bold><blue>' . $colArray['autoincrement'] ? 'X</bold></blue>' : ' </bold></blue>',
                '<bold><white>UN</white></bold>' =>
                    '<bold><blue>' . $colArray['unsigned'] ? 'X</bold></blue>' :' </bold></blue>',
                '<bold><white>Dft</white></bold>' =>
                    '<bold><blue>' . $colArray['default']. '</bold></blue>',
                '<bold><white>Cmnt</white></bold>' =>
                    '<bold><blue>' . chunk_split($colArray['comment'] ?? '', 15) . '</bold></blue>'
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
        CliBase::getCli()->tbl()->table($showDetails);
    }
}
