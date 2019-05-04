<?php
declare(strict_types=1);

namespace Willow\Robo;

class Script
{
    // You would think that postPackageInstall fires only once (at the end of the install -- you'd be wrong!)
    public static function postPackageInstall($event)
    {
        $path = __DIR__ . '/../../vendor/bin/robo';
        if (file_exists($path)) {
            if (!file_exists(__DIR__ . '/../../willow')) {
                symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
            }
        }
    }
}
