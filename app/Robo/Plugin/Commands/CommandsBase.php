<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\TerminalObject\Dynamic\Input;

abstract class CommandsBase
{
    private const DOT_ENV_PATH = __DIR__ . '/../../../../.env';
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';

    protected static function showColumns(string $tableName) {
        $tabDetails = DatabaseUtilities::getTableDetails($tableName);
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
     * Ask user what table they want to use
     * @return string
     * @throws Exception
     */
    public static function getUserTableSelection(): string {
        $tables = DatabaseUtilities::getTableList();
        /** @var Input $input */
        $input = CliBase::getCli()->radio('Select a table', $tables);
        return $input->prompt();
    }

    protected function checkEnvLoaded() {
        // Does the .env file not exist? If not then prompt user and create
        if (!file_exists(self::DOT_ENV_PATH)) {
            CliBase::billboard('make-env', 160, 'top');
            $input = CliBase::getCli()->bold()->white()->input('Press enter to start. Ctrl-C to quit.');
            $input->prompt();
            CliBase::billboard('welcome', 160, '-top');
            CliBase::getCli()->clear();
            UserReplies::setEnvFromUser();
        }

        // If the .env file is not loaded then load it now.
        if (strlen($_ENV['DB_DRIVER'] ?? '') === 0) {
            include_once self::DOT_ENV_INCLUDE_FILE;
        }
    }
}
