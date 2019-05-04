<?php
declare(strict_types=1);

namespace Willow\Robo;

/**
 * Composer script
 */
class Script
{
    // Create the willow symlink to Robo
    public static function postPackageInstall($event)
    {
        $path = __DIR__ . '/../../vendor/bin/robo';
        // Has Robo been fully installed?
        if (file_exists($path)) {
            // Does the willow file NOT exist?
            if (!file_exists(__DIR__ . '/../../willow')) {
                // Create the willow symlink file
                symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
            }
        }
    }
}
