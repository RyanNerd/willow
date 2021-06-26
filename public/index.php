<?php
declare(strict_types=1);

// phpcs:ignoreFile -- We are breaking some conventions intentionally for performance and usability purposes.

use DI\ContainerBuilder;
use Willow\Robo\Plugin\Commands\CliBase;
use Willow\Willow;

require __DIR__ . '/../vendor/autoload.php';

try {
    // Establish DI
    $builder = new ContainerBuilder();

    // If the .env file exists then load and verify it.
    if (file_exists(__DIR__ . '/../.env')) {
        include_once __DIR__ . '/../config/_env.php';

        // Are we in production?
        if ($_ENV['PRODUCTION'] ?? '' === 'true') {
            // Since this is production we enable DI compilation and caching of DI proxies
            $builder
                ->addDefinitions(__DIR__ . '/../config/db.php')
                ->enableCompilation(__DIR__ . '/tmp/cache')
                ->writeProxiesToFile(true, __DIR__ . '/tmp/cache');
        } else {
            // Non-production environment so no DI compilation or caching
            $builder
                ->addDefinitions(__DIR__ . '/../config/db.php');
        }
    }

    // Build the DI container
    $container = $builder->build();

    // If Eloquent is defined then instantiate it.
    if ($container->has('Eloquent')) {
        $container->get('Eloquent');
    }
} catch (Throwable $throwable) {
    // See: https://github.com/krakjoe/pthreads/issues/806
    if (!defined('STDERR')) {
        define('STDERR', fopen('php://stderr', 'wb'));
    }
    if (!defined('STDOUT')) {
        define('STDOUT', fopen('php://stdout', 'wb'));
    }
    CliBase::showThrowableAndDie($throwable);
}

// Launch the app
(new Willow($container));
