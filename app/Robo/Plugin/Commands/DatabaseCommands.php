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
        $tables = DatabaseUtilities::getTableList();

        // Ask the user what table to get details for
        $cli = $this->cli;
        /** @var Input $input */
        $input = $cli->radio('Select a table', $tables);
        $tableName = $input->prompt();

        $dbIndexes = DatabaseUtilities::getTableIndexes($tableName);
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
            CliBase::getCli()->red()->backgroundLightGray('Unable to determine index keys for: ' . $tableName);
        }
    }

    /**
     * Show column details for a selected table
     * @throws Exception
     */
    final public function dbColumns() {
        $tables = DatabaseUtilities::getTableList();

        // Ask the user what table to get details for
        $cli = $this->cli;
        /** @var Input $input */
        $input = $cli->radio('Select a table', $tables);
        $tableName = $input->prompt();

        $tabDetails = DatabaseUtilities::getTableDetails($tableName);
        $columns = $tabDetails->getColumns();
        $colDetails = [];
        foreach ($columns as $column) {
            $colArray = $column->toArray();
            $colDetails[] = [
                'Column' => $colArray['name'],
                'Type' => $colArray['type']->getName(),
                'Length' => $colArray['length'],
                'NN' => $colArray['notnull'],
                'AI' => $colArray['autoincrement'],
                'UN' => $colArray['unsigned'],
                'Default' => $colArray['default'],
                'Comment' => chunk_split($colArray['comment'] ?? '', 15)
            ];
        }
        CliBase::getCli()->table($colDetails);
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
}
