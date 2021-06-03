<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Slim\Routing\RouteCollectorProxy;
use Willow\Controllers\Sample\SampleController;

class RegisterRouteControllers
{
    protected SampleController $sampleController;

    public function __construct(SampleController $sampleController)
    {
        $this->sampleController = $sampleController;
    }

    public function __invoke(RouteCollectorProxy $collectorProxy): self
    {
        // TODO: Use Twig to build this out

        // Register routes and actions for each controller
        $this->sampleController->register($collectorProxy);

        return $this;
    }
}