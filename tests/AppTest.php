<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Willow\Middleware\RegisterControllers;
use Willow\Willow;
use Slim\Routing\RouteCollectorProxy;

final class AppTest extends TestCase
{
    public function testApp(): void
    {
        /** @var \DI\Container $mockContainer */
        $mockContainer = $this->createMock(DI\Container::class);
//        $mockSampleController = $this->createMock(\Willow\Controllers\Sample\SampleController::class);
        $mockRegisterControllers = $this->getMockBuilder(RouteCollectorProxy::class)->disableOriginalConstructor()->getMock();
        $mockWillow = $this->getMockBuilder(Willow::class)->setConstructorArgs([$mockContainer])->addMethods(['group'])->getMock();
        $mockWillow->expects($this->once())->method('group')->with('/v1', RegisterControllers::class);
        $mockWillow->run();

//      $mockRegisterControllers = $this->createMock(RegisterControllers::class);
//      group('/v1', RegisterControllers::class);


    }
}
