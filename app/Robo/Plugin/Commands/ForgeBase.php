<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;

abstract class ForgeBase
{
    protected const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    /**
     * Called when an exception is encountered.
     * @param Throwable $throwable
     */
    protected function forgeError(Throwable $throwable) {
        RoboBase::showThrowableAndDie($throwable);
    }
}
