<?php
declare(strict_types=1);

namespace Willow\Hello;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class HelloController
{
    public function registerGroup(RouteCollectorProxy $group)
    {
        $group->get('/hello/{id}', HelloGetAction::class);
            //->add(HelloGetValidator::class);
    }

    public function register(App $app)
    {
        $app->get('/hello/{id}', HelloGetAction::class);
            //->add(HelloGetValidator::class);
    }
}
