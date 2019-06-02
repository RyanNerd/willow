<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Willow\Main\App;

final class AppTest extends TestCase
{
    public function testApp(): void
    {
        $app = new App(false);

        $this->assertInstanceOf(App::class, $app);
    }
}

