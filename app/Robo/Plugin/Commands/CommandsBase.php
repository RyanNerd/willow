<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Doctrine\DBAL\Exception;
use League\CLImate\TerminalObject\Dynamic\Input;

abstract class CommandsBase
{
    private const DOT_ENV_INCLUDE_FILE = __DIR__ . '/../../../../config/_env.php';
    private const DOT_ENV_PATH = __DIR__ . '/../../../../.env';
    protected const VIRIDIAN_PATH = __DIR__ . '/../../../../.viridian';

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

    /**
     * Check if the .env file exists and if not prompt user to create it then load and validate.
     */
    protected function checkEnvLoaded(): void {
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
