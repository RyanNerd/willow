<?php
declare(strict_types=1);

namespace Willow\Robo;

use League\CLImate\CLImate;

/**
 * Composer script
 */
class Script
{
    /**
     * Composer hook that fires when composer create-project has executed.
     *
     * @param $event
     */
    public static function postCreateProjectCmd($event)
    {
        // Figure out what directory was created most recently
        $time = 0;
        $projectName = 'your-project-name';
        foreach(glob(__DIR__ . '/*',GLOB_ONLYDIR) as $dir) {
            $cTime = filectime($dir);
            if ($cTime !== false && $cTime > $time) {
                $time = $cTime;
                $projectName = basename($dir);
            }
        }

        // Display Willow's fancy message
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

        // Create symlink to Robo
        $path = __DIR__ . '/../../vendor/bin/robo';
        // Has Robo been fully installed?
        if (file_exists($path)) {
            // Does the willow file NOT already exist?
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
