<?php
declare(strict_types=1);

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

return [
    IlluminateContainer::class => new IlluminateContainer,
    Dispatcher::class => autowire(Dispatcher::class)->constructor(get(IlluminateContainer::class)),
    'Eloquent' =>
        function (ContainerInterface $c) {
            $eloquent = new Manager($c->get(IlluminateContainer::class));
            $eloquent->addConnection([
                'driver'    => 'mysql',
                'host'      => $_ENV['DB_HOST'],
                'port'      => $_ENV['DB_PORT'],
                'database'  => $_ENV['DB_NAME'],
                'username'  => $_ENV['DB_USER'],
                'password'  => $_ENV['DB_PASSWORD'],
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => ''
            ]);
            // If we want events to work we need to do this
            // Link: https://stackoverflow.com/a/35274727/4323201
            $eloquent->setEventDispatcher($c->get(Dispatcher::class));
            $eloquent->setAsGlobal();
            $eloquent->bootEloquent();
            return $eloquent->setFetchMode(PDO::FETCH_ASSOC);
        }
];
