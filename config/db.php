<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

return [
    'Eloquent' => function () {
        if (!file_exists(__DIR__ . '/../.env')) {
            throw new Exception('Missing required .env file');
        }

        $eloquent = new Manager();

        // @see https://github.com/illuminate/database/blob/master/README.md
        $eloquent->addConnection([
            'driver'    => $_ENV['DB_DRIVER'],
            'host'      => $_ENV['DB_HOST'] ?? '',
            'port'      => $_ENV['DB_PORT'] ?? '',
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'] ?? '',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]);

        // If we want events to work we need to do this
        // Link: https://stackoverflow.com/a/35274727/4323201
        $eloquent->setEventDispatcher(new Dispatcher(new Container()));

        // Make this Capsule instance available globally via static methods
        $eloquent->setAsGlobal();

        // Setup the Eloquent ORM...
        $eloquent->bootEloquent();

        // Set the fetch mode to return associative arrays.
        $eloquent->setFetchMode(PDO::FETCH_ASSOC);

        return $eloquent;
    }
];
