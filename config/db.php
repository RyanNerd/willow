<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

return [
    'db.driver' => (getenv('DB_DRIVER') === false) ? 'mysql' : getenv('DB_DRIVER'),
    'db.dsn' => (getenv('DB_DRIVER') === false) ? 'mysql' : getenv('DB_DRIVER') . ':host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_NAME'),
    'db.host' => getenv('DB_HOST'),
    'db.user' => getenv('DB_USER'),
    'db.password' => getenv('DB_PASSWORD'),
    'db.schema' => getenv('DB_NAME'),

    Capsule::class => function (ContainerInterface $c) {
        $eloquent = new Capsule;

        $eloquent->addConnection([
            'driver'    => $c->get('db.driver'),
            'host'      => $c->get('db.host'),
            'database'  => $c->get('db.schema'),
            'username'  => $c->get('db.user'),
            'password'  => $c->get('db.password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
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
