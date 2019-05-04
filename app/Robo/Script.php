<?php
declare(strict_types=1);

namespace Willow\Robo;

use League\CLImate\CLImate;

/**
 * Composer script
 */
class Script
{
    public static function postCreateProjectCmd($event)
    {
        $cli = new CLImate();
        $cli->forceAnsiOn();
        $cli->addArt(__DIR__);
        $cli->green()->border('*', 45);
        $cli->bold()->lightGreen()->animation('willow')->speed(200)->enterFrom('left');
        $cli->backgroundGreen()->lightGray(' 🌳 https://github.com/RyanNerd/willow 🌳');
        $cli->green()->border('*', 45);
        $cli->bold()->white()->inline('Thanks for installing ');
        $cli->bold()->lightGreen()->inline('Willow');
        $cli->bold()->white('!');

        $path = __DIR__ . '/../../vendor/bin/robo';
        // Has Robo been fully installed?
        if (file_exists($path)) {
            // Does the willow file NOT exist?
            if (!file_exists(__DIR__ . '/../../willow')) {
                // Create the willow symlink file
                symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $cli->bold()->white('To get started type: willow help');
            } else {
                $cli->bold()->white('To get started type: ./willow help');
            }
        }
    }
}
