<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Input;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks
{
    /** @var CLImate */
    protected $cli;

    /** @var Capsule */
    protected $capsule;

    use \Robo\Template\Generator;

    public function __construct()
    {
        $this->cli = new CLImate;

        // Set up DI and ORM only if the .env file exists.
        if (file_exists(__DIR__ . '/.env')) {
            // Set up Dependency Injection
            try {
                $builder = new ContainerBuilder();
                foreach (glob(__DIR__ . '/config/*.php') as $definitions) {
                    if (strpos($definitions, '_env.php') === false) {
                        $builder->addDefinitions(realpath($definitions));
                    }
                }
                $container = $builder->build();
            } catch (\Throwable $exception) {
                $this->okay = $exception;
                return;
            }

            // Establish an instance of the Illuminate database capsule (if not already established)
            try {
                if ($this->capsule === null) {
                    $this->capsule = $container->get(Capsule::class);
                }
            } catch (\Throwable $exception) {
                $this->okay = $exception;
                return;
            }
        }
    }


    public function init()
    {
        $cli = $this->cli;

        $this->taskComposerDumpAutoload()->run();

        if (file_exists(__DIR__ . '/.env')) {
            $cli->bold()->blink()->inline('WARNING: ');
            $cli->error('A .env file already exists.');
            /** @var Input $input */
            $input = $cli->confirm('Are you sure you want to proceed?');
            $input->defaultTo('n');
            if ($input->confirmed()) {
                $cli->bold()->white('Enter values for the .env file');
                $input = $cli->input('DB_HOST (ex: 127.0.0.1)');
                $dbHost = $input->prompt();
                $input = $cli->input('DB_PORT (ex: 3306)');
                $dbPort = $input->prompt();
                // TODO; validate port
                $input = $cli->input('DB_NAME');
                $dbName = $input->prompt();
                $input = $cli->input('DB_USER');
                $dbUser = $input->prompt();
                $input = $cli->password('DB_PASSWORD');
                $dbPassword = $input->prompt();
                $input = $cli->input('DISPLAY_ERROR_DETAILS (true/false)');
                $displayErrorDetails = $input
                    ->accept(['true','false'])
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

                $cli->br();
                $cli->bold()->white()->border();
                $cli->white($envText);
                $cli->bold()->white()->border();
                $cli->br();
                $input = $cli->confirm('Create .env');
                if ($input->confirmed()) {
                    $spinner = $this->cli->backgroundLightRed()->white()->spinner('Working');
                    foreach (range(0, 10000) as $i) {
                        $spinner->advance();
                        usleep(500);
                    }
                }
            } else {
                $cli->bold()->white('TODO: Set up initialization');
            }
        }
    }

    /**
     * Traditional Hello World as a task
     * @param string $world
     */
    public function hello(string $world)
    {
        $this->cli->blue()->bold()->inline("Hello, ")->red()->out($world);
    }

    public function showTables()
    {
        $capsule = $this->capsule;
        $conn = $capsule->getConnection();
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
}