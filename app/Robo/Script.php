<?php
declare(strict_types=1);

namespace Willow\Robo;

class Script
{
    public static function postPackageInstall($event)
    {
        $absolutePath = __DIR__ . '../../vendor/consolidation/robo/robo';
        $path = __DIR__ . '/../../vendor/bin/robo';
        if (!file_exists($path)) {
            if (file_exists($absolutePath)) {
                symlink($absolutePath, 'willow');
            } else {
                echo PHP_EOL . 'NOW WHAT?!?' . PHP_EOL;
            }
        } else {
            symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
        }
    }
}
