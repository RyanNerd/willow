<?php
declare(strict_types=1);

namespace Willow\Controllers;

use Slim\Interfaces\RouteCollectorProxyInterface;

Interface IController
{
    public function register(RouteCollectorProxyInterface $group);
}
