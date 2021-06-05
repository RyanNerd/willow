<?php
declare(strict_types=1);

use Willow\Main\App;
use DI\ContainerBuilder;
use League\CLImate\CLImate;

require __DIR__ . '/../vendor/autoload.php';

try {
    // Establish DI
    $builder = new ContainerBuilder();
    if (file_exists(__DIR__ . '/../.env')) {
        $builder
            ->addDefinitions(['DEMO' => false])
            ->addDefinitions(__DIR__ . '/../config/_env.php')
            ->addDefinitions(__DIR__ . '/../config/db.php');
    } else {
        $builder->addDefinitions(['DEMO' => true]);
    }

    $container = $builder->build();

    if (!$container->get('DEMO')) {
        // Load and validate .env
        $container->get('ENV');
        // Instantiate Eloquent ORM
        $container->get('Eloquent');
    }
} catch (Throwable $throwable) {
    // See: https://github.com/krakjoe/pthreads/issues/806
    if (!defined('STDOUT')) {
        define('STDOUT', fopen('php://stdout', 'wb'));
    }

    if (!defined('STDERR')) {
        define('STDERR', fopen('php://stderr', 'wb'));
    }

    $cli = new CLImate();
    $cli->to('error')->br(2);
    $cli->to('error')->red('Message')->white($throwable->getMessage());
    $cli->to('error')->red('File: ')->white($throwable->getFile());
    $cli->to('error')->red('Line: ')->white((string)$throwable->getLine());
    $cli->to('error')->red('Trace: ')->white($throwable->getTraceAsString());
    $cli->to('error')->br(2);

    $cli->to('out')->br(2);
    $cli->to('out')->red('Message')->white($throwable->getMessage());
    $cli->to('out')->red('File: ')->white($throwable->getFile());
    $cli->to('out')->red('Line: ')->white((string)$throwable->getLine());
    $cli->to('out')->red('Trace: ')->white($throwable->getTraceAsString());
    $cli->to('out')->br(2);
    exit();
}

// Launch the app
new App($container);
