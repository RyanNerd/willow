<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Willow\Willow;

final class AppTest extends TestCase
{
    public function testApp(): void
    {
        /** @var \DI\Container $mockContainer */
        $mockContainer = $this->createMock(DI\Container::class);
        $app = new Willow($mockContainer);

        $this->assertInstanceOf(Willow::class, $app);
    }
}
