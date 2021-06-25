<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\TerminalObject\Dynamic\Input;

abstract class CommandsBase
{
    protected static function showColumns(string $tableName) {
        $tabDetails = DatabaseUtilities::getTableDetails($tableName);
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
}
