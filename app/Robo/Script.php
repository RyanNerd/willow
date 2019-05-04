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
        $arguments = $event->getArguments() ?? [];
        $argCount = count($arguments);
        if ($argCount > 0) {
            $projectName = $arguments[$argCount -1];
        } else {
            $projectName = 'your-project-name';
        }

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

            $cli->bold()->white('To get started type:');
            $cli->bold()->lightGray('cd ' . $projectName);
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $cli->bold()->lightGray('willow list');
            } else {
                $cli->bold()->lightGray('./willow list');
            }
        }
    }
}
