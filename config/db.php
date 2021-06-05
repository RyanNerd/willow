<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;

return [
    'Eloquent' => function (ContainerInterface $c) {
        if (!$c->has('ENV')) {
            die('.env file missing or corrupt.');
        }

        $eloquent = new Manager();
        $env = $c->get('ENV');
        $eloquent->addConnection([
            'driver'    => $env['DB_DRIVER'],
            'host'      => $env['DB_HOST'],
            'port'      => $env['DB_PORT'],
            'database'  => $env['DB_NAME'],
            'username'  => $env['DB_USER'],
            'password'  => $env['DB_PASSWORD'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]);

        // Make this Capsule instance available globally via static methods
        $eloquent->setAsGlobal();

        // Setup the Eloquent ORM...
        $eloquent->bootEloquent();

        // Set the fetch mode to return associative arrays.
        $eloquent->setFetchMode(PDO::FETCH_ASSOC);

        return $eloquent;
    }
];
