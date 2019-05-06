<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Confirm;
use Respect\Validation\Validator as V;

class InitCommands extends RoboBase
{
    /**
     * Initialization of the Willow framework specifically the .env file
     */
    public function willowInit()
    {
        $cli = $this->cli;

        $envPath = __DIR__ . '/../../../../.env';
        if (file_exists($envPath)) {
            $this->warning('A .env file already exists.');
            $cli->out('Delete the .env file and re-run this command or manually edit the .env file');
            return;
        }

        do {
            $cli->bold()->white('Enter values for the .env file');
            do {
                /** @var Confirm $input */
                $input = $cli->input('DB_HOST (ex: 127.0.0.1)');
                $input->defaultTo('127.0.0.1');
                $dbHost = $input->prompt();
            } while(strlen($dbHost) === 0);

            do {
                $input = $cli->input('DB_PORT (ex: 3306)');
                $input->defaultTo('3306');
                $dbPort = $input->prompt();
            } while(strlen($dbPort) === 0 || (int)$dbPort <= 0 || (int)$dbPort > 65535);

            do {
                $input = $cli->input('DB_NAME');
                $dbName = $input->prompt();
            } while(strlen($dbName) === 0);

            do {
                $input = $cli->input('DB_USER');
                $dbUser = $input->prompt();
            } while(strlen($dbUser) === 0);

            do {
                $input = $cli->password('DB_PASSWORD');
                $dbPassword = $input->prompt();
            } while(strlen($dbPassword) === 0);

            $input = $cli->input('DISPLAY_ERROR_DETAILS (true/false)');
            $displayErrorDetails = $input
                ->accept(['true', 'false'])
                ->defaultTo('false')
                ->prompt();

            $envText = <<<env
DB_HOST=$dbHost
DB_PORT=$dbPort
DB_NAME=$dbName
DB_USER=$dbUser
DB_PASSWORD=$dbPassword
DISPLAY_ERROR_DETAILS=$displayErrorDetails

env;

            $envText = str_replace("\n\r", "\n", $envText);
            $envLines = explode("\n", $envText);
            $obfuscatedEnv = "";
            foreach ($envLines as $line) {
                if (strlen($line) === 0) {
                    continue;
                }

                if (strpos($line, 'DB_PASSWORD=') > 0) {
                    $obfuscatedEnv .= 'DB_PASSWORD=********' . PHP_EOL;
                } else {
                    $obfuscatedEnv .= $line . PHP_EOL;
                }
            }

            $cli->br();
            $cli->bold()->white()->border();
            $cli->white($obfuscatedEnv);
            $cli->bold()->white()->border();
            $cli->br();
            $this->warning('Make sure the information is correct.');
            $input = $cli->confirm('Create .env?');
        } while (!$input->confirmed());

        $spinner = $this->cli->white()->spinner('Working');
        foreach (range(0, 2000) as $i) {
            $spinner->advance();
            usleep(500);
        }

        if (file_put_contents($envPath, $envText) !== false) {
            $cli->out('.env file created');
        } else {
            $this->error('Unable to create .env file');
        }
    }

    /**
     * Eject the Willow framework from the project
     */
    public function willowEject()
    {
        $cli = $this->cli;
        $cli->br();
        $cli->bold()->white('Running eject will do the following things:');
        $monolog = <<<MONOLOG
- Remove app/Controllers/Sample folder (if it exists)
- Remove app/Robo folder and sub folders
- Remove RoboFile.php
- Remove Robo as a dependency from composer.json
- Prompt you for a project name (with no spaces)
- Replace ALL namespace instances of Willow with the entered project name
- Update composer.json with the new namespace/project name
- Remove env-example
- Remove the willow symlink to the Robo task runner
- Remove composer.lock
- Run `composer install` to sort out the new namespace and dependencies

MONOLOG;

        $cli->bold()->red($monolog);
        $this->warning('THIS CAN NOT BE UNDONE!');
        /** @var Confirm $input */
        $input = $cli->confirm('You sure you want to do this?');
        if ($input->confirmed()) {
            // TODO: Do eject stuff
            $this->warning('Not implemented');
            $input = $cli->input('Enter the project name (alpha-numeric no whitespace)');
            $project = $input->prompt();

            if (!v::alnum()->noWhitespace()->validate($project)) {
                $this->warning('Invalid project name: ' . $project);
                return;
            }
        }
    }
}