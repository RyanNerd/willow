<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Confirm;
use Respect\Validation\Validator as V;
use Robo\Task\Development\PhpServer;
use Willow\Robo\Script;

class WillowCommands extends RoboBase
{
    /**
     * @var PhpServer
     */
    private $server;

    /**
     * Initialization of the Willow framework specifically the .env file
     */
    public function willowInit(): void
    {
        $cli = $this->cli;

        $envPath = __DIR__ . '/../../../../.env';
        if (file_exists($envPath)) {
            $this->warning('A .env file already exists.');
            $cli->out('Delete the .env file and re-run this command or manually edit the .env file');
            return;
        }

        $cli->bold()->white('Enter values for the .env file');

        if (self::isWindows()) {
            $drivers = ['mysql', 'pgsql', 'sqlsrv', 'sqlite'];

            do {
                $cli->out('Driver must be one of: ' . implode(', ', $drivers));

                /** @var Confirm $input */
                $input = $cli->input('DB_DRIVER (default: mysql)');
                $input->defaultTo('mysql');
                $dbDriver = $input->prompt();
            } while (!in_array($dbDriver, $drivers));
        } else {
            do {
                $drivers = [
                    'MySQL/MariaDB' => 'mysql',
                    'Postgres' => 'pgsql',
                    'MS SQL' => 'sqlsrv',
                    'SQLite' => 'sqlite'
                ];

                $driverChoices = array_keys($drivers);
                /** @var Confirm $input */
                $input = $cli->radio('Select database driver', $driverChoices);
                $driverSelection = $input->prompt();
                $dbDriver = $drivers[$driverSelection];
            } while (strlen($dbDriver) === 0);
        }

        do {
            if ($dbDriver === 'sqlite') {
                $dbHost = '';
                $dbPort = '';
                $dbUser = '';
                $dbPassword = '';
            } else {
                do {
                    $input = $cli->input('DB_HOST (default: 127.0.0.1)');
                    $input->defaultTo('127.0.0.1');
                    $dbHost = $input->prompt();
                } while(strlen($dbHost) === 0);

                do {
                    $input = $cli->input('DB_PORT (default: 3306)');
                    $input->defaultTo('3306');
                    $dbPort = $input->prompt();
                } while(strlen($dbPort) === 0 || (int)$dbPort <= 0 || (int)$dbPort > 65535);

                do {
                    $input = $cli->input('DB_USER');
                    $dbUser = $input->prompt();
                } while(strlen($dbUser) === 0);

                do {
                    $input = $cli->password('DB_PASSWORD');
                    $dbPassword = $input->prompt();
                } while(strlen($dbPassword) === 0);
            }

            do {
                $input = $cli->input('DB_NAME');
                $dbName = $input->prompt();
            } while(strlen($dbName) === 0);

            $input = $cli->input('DISPLAY_ERROR_DETAILS (true/false)');
            $displayErrorDetails = $input
                ->accept(['true', 'false'])
                ->defaultTo('false')
                ->prompt();

            $envText = <<<env
DB_DRIVER=$dbDriver            
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

                if (strstr($line, 'DB_PASSWORD')) {
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
    public function willowEject(): void
    {
        $cli = $this->cli;
        $cli->br();
        $cli->bold()->white('Running eject will do the following things:');
        $monolog = <<<MONOLOG
- Prompt you for a project name (with no spaces)
- Remove app/Controllers/Sample folder and unit tests (if they exist)
- Remove the willow symlink to the Robo task runner
- Replace ALL namespace instances of Willow with the entered project name
- Update composer.json with the new namespace/project name
- Remove composer.lock
- Composer will resolve dependencies
- Prompt you with manual tasks to complete the ejection 

MONOLOG;

        $cli->bold()->red($monolog);
        $this->warning('THIS CAN NOT BE UNDONE!');
        /** @var Confirm $input */
        $input = $cli->confirm('Are you sure you want to do this?');
        if ($input->confirmed()) {
            $input = $cli->input('Enter the project name (alpha-numeric no whitespace)');
            $project = $input->prompt();

            if (!v::alnum()->noWhitespace()->validate($project)) {
                $this->warning('Invalid project name: ' . $project);
                return;
            }

            // Remove the Sample
            $sampleDir = __DIR__ . '/../../../Controllers/Sample';
            if (is_dir($sampleDir)) {
                $this->taskDeleteDir($sampleDir)->run();
            }

            // Remove the Sample unit tests
            $sampleTestPath = '/../../../../tests/SampleTest.php';
            if (file_exists($sampleTestPath)) {
                unlink($sampleTestPath);
            }

            // Remove the willow symlink
            $willowPath = __DIR__ . '/../../../../willow';
            if (is_file($willowPath)) {
                unlink($willowPath);
            }

            // Update every *.php file to use the new project namespace
            $phpFiles = $this->getFiles(__DIR__ . '/../../../../app', 'php');
            $this->updateProjectName($phpFiles, $project);
            $phpFiles = $this->getFiles(__DIR__ . '/../../../../tests', 'php');
            $this->updateProjectName($phpFiles, $project);
            $phpFiles = $this->getFiles(__DIR__ . '/../../../../public', 'php');
            $this->updateProjectName($phpFiles, $project);

            // Update composer.json to use the new project name
            $composerPath = __DIR__ . '/../../../../composer.json';
            $composerText = file_get_contents($composerPath);
            $composerText = str_replace('ryannerd/willow', $project, $composerText);
            $composerText = str_replace('Willow Framework for creating ORM/RESTful APIs', $project, $composerText);
            $composerText = str_replace('Willow', $project, $composerText);
            file_put_contents($composerPath, $composerText);

            // Destroy the composer.lock file to force composer to resolve dependencies
            $composerLockPath = __DIR__ . '/../../../../composer.lock';
            if (is_file($composerLockPath)) {
                unlink($composerLockPath);
            }

            $cli->br();

            // Animation in Windows chokes on preg_replace for some reason.
            if (!self::isWindows()) {
                $cli->addArt(__DIR__ . '/../');
                $cli->green()->animation('willow')->exitTo('left');
            }

            $cli->bold()->white('Some things must be manually done:');
            if (self::isWindows()) {
                $cli->bold()->yellow('Run: `rmdir app/Robo /s` to destroy the Robo folder.');
            } else {
                $cli->bold()->yellow('Run: `rm -rf app/Robo` to destroy the Robo folder.');
            }
            $cli->bold()->yellow('Manually edit composer.json and remove the `post-create-project-cmd` script and make any other changes.');
            $cli->bold()->yellow('If you are not going to use Robo in your project you can run `composer remove "consolidation/robo"`');
            $cli->bold()->yellow('If you are removing Robo you can safely delete RoboFile.php');
        }
    }

    /**
     * Launch the Willow Framework User's Guide in the default web browser
     */
    public function willowDocs(): void
    {
        $this->taskOpenBrowser('https://willow.plexie.com/app/#/public/project/f66cdc9e-18dd-419c-8575-0c8901152cd3/board/0392b5a8-a2db-4e4b-831e-ebb4aa65fb13')->run();
    }

    /**
     * Launch the built-in PHP web server and launch the Sample in a web browser
     */
    public function willowSample(): void
    {
        $server = $this->taskServer(8088);
        $this->server = $server->host('127.0.0.1')
            ->dir(__DIR__ . '/../../../../public')
            ->background()
            ->run();

        $this->taskOpenBrowser('http://localhost:8088/v1/sample/Hello-World')->run();
        sleep(20);
    }

    /**
     * Show Willow's fancy banner
     */
    public function willowBanner(): void
    {
        Script::fancyBanner();
    }

    /**
     * Update the project namespaces
     *
     * @param array $phpFiles
     * @param string $project
     */
    private function updateProjectName(array $phpFiles, string $project): void
    {
        foreach ($phpFiles as $phpFile) {
            // Ignore those files in /app/Robo
            if (strstr($phpFile, '/app/Robo/')) {
                continue;
            }

            $fileText = file_get_contents($phpFile);
            $fileText = str_replace('use Willow', 'use '. $project, $fileText);
            $fileText = str_replace('namespace Willow', 'namespace '. $project, $fileText);
            file_put_contents($phpFile, $fileText);
        }
    }

    /**
     * Get all the files given a directory path
     *
     * @param string $dir
     * @param string $ext
     * @return array
     */
    private function getFiles(string $dir, string $ext = ''): array
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()){
                continue;
            }

            $filePath = realpath($file->getPathname());

            if ($ext !== '') {
                if (pathinfo($filePath, PATHINFO_EXTENSION) !== $ext) {
                    continue;
                }
            }

            $files[] = $filePath;
        }
        return $files;
    }
}