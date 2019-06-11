<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;

return [
    Capsule::class => function (ContainerInterface $c) {
        $eloquent = new Capsule;

        $eloquent->addConnection([
            'driver'    => env('DB_DRIVER') ?? 'mysql',
            'host'      => getenv('DB_HOST'),
            'port'      => getenv('DB_PORT') ?? '',
            'database'  => getenv('DB_NAME'),
            'username'  => getenv('DB_USER'),
            'password'  => getenv('DB_PASSWORD'),
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
