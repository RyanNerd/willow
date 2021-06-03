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

    /**
     * Register routes and actions for each controller
     * @param RouteCollectorProxy $collectorProxy
     * @return $this
     */
    public function __invoke(RouteCollectorProxy $collectorProxy): self
    {
        // DEMO only
        $container = $collectorProxy->getContainer();
        if ($container->get('DEMO')) {
            $this->sampleController->register($collectorProxy);
        }

        return $this;
    }
}