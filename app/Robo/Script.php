<?php
declare(strict_types=1);

namespace Willow\Robo;

class Script
{
    public static function postPackageInstall($event)
    {
        $path = __DIR__ . '/../../vendor/bin/robo';
        echo $path;
        symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
    }
}
