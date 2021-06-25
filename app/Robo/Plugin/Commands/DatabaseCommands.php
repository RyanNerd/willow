<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;

class DatabaseCommands
{
    private const DOT_ENV_FILE = __DIR__ . '/../../../../.env';
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';
    private CLImate $cli;

    public function __construct() {
        $this->cli = CliBase::getCli();
        $this->checkEnvLoaded();
    }

    /**
     * Show all the tables
     */
    final public function dbTables(): void {
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
        $dbIndexes = DatabaseUtilities::getTableIndexes($this->getUserTableSelection());
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
     * @throws Exception
     */
    final public function dbColumns() {
        $tabDetails = DatabaseUtilities::getTableDetails($this->getUserTableSelection());
        $pk = $tabDetails->getPrimaryKey();
        $pkColumns = $pk->getColumns();
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
        $tableDetails = DatabaseUtilities::getTableDetails($this->getUserTableSelection());
        $options = $tableDetails->getOptions();
        $fk = $tableDetails->getForeignKeys();
        $pk = $tableDetails->getPrimaryKey();
        $pkName = $pk->getName();
        $pkColumns = implode(',', $pk->getColumns());

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
            if (is_string($value)) {
                $showDetails[] = ['Name' => $key, 'Setting' => $value];
            }
        }

        foreach ($fk as $key => $value) {
            $showDetails[] = [
                'Name' => 'Local (' . $value->getName() . ')',
                'Setting' => implode(',', $value->getLocalColumns())
            ];
            $showDetails[] = ['Name' => 'Foreign Table', 'Setting' => $value->getForeignTableName()];
            $showDetails[] = ['Name' => 'Foreign Column(s)', 'Setting' => implode(',', $value->getForeignColumns())];
        }
        CliBase::getCli()->br()->table($showDetails);
    }

    /**
     * Check if the .env file exists and has been loaded, if not prompt the user and build the .env file.
     */
    private function checkEnvLoaded() {
        if (!file_exists(self::DOT_ENV_FILE)) {
            $cli = CliBase::getCli();
            CliBase::billboard('welcome', 160, 'top');
            $input = $cli->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('welcome', 160, '-top');
            $cli->clear();

            UserReplies::setEnvFromUser();
        }

        // If the .env file is not loaded then load it now.
        if (strlen($_ENV['DB_DRIVER'] ?? '') === 0) {
            include_once self::DOT_ENV_INCLUDE_FILE;
        }
    }

    /**
     * Ask user what table they want to use
     * @return string
     * @throws Exception
     */
    private function getUserTableSelection(): string {
        $tables = DatabaseUtilities::getTableList();
        $cli = $this->cli;
        /** @var Input $input */
        $input = $cli->radio('Select a table', $tables);
        return $input->prompt();
    }
}
