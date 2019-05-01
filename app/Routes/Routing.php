<?php
declare(strict_types=1);

namespace Willow\Routes;

use Slim\App;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\RouteCollectorProxy;
use Willow\Controllers\Hello\HelloController;

Trait Routing
{
    private function registerGroupRoutes(App $app, string $groupPattern): RouteGroupInterface
    {
        $container = $app->getContainer();
        return $app->group($groupPattern, function (RouteCollectorProxy $collectorProxy) use ($container)
        {
            $container->get(HelloController::class)->registerGroup($collectorProxy);
        });
    }

    private function registerRoutes(App $app)
    {
        $container = $app->getContainer();
        $container->get(HelloController::class)->register($app);
    }
}