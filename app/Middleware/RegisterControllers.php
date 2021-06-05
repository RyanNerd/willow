<?php
declare(strict_types=1);

namespace Willow\Middleware;

use Slim\Routing\RouteCollectorProxy;
use Willow\Controllers\Sample\SampleController;

class RegisterControllers
{
    protected SampleController $sampleController;

    public function __construct(SampleController $sampleController)
    {
        $this->sampleController = $sampleController;
    }

    /**
    * Register routes and actions for each controller
    * @param RouteCollectorProxy $collectorProxy
    * @return self
    */
    public function __invoke(RouteCollectorProxy $collectorProxy): self
    {
        $this->sampleController->register($collectorProxy);
        return $this;
    }
}
