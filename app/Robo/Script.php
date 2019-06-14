<?php
declare(strict_types=1);

namespace Willow\Robo;

use League\CLImate\CLImate;
use Willow\Robo\Plugin\Commands\WillowCommands;

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
    public static function postCreateProjectCmd($event): void
    {
        // Figure out what directory was created most recently
        $time = 0;
        $projectName = 'your-project-name';
        foreach(glob(__DIR__ . '/../../../*',GLOB_ONLYDIR) as $dir) {
            $cTime = filectime($dir);
            if ($cTime !== false && $cTime > $time) {
                $time = $cTime;
                $projectName = basename($dir);
            }
        }

        // Display Willow's fancy message
        self::fancyBanner();

        // Create symlink to Robo
        $path = __DIR__ . '/../../vendor/bin/robo';
        // Has Robo been fully installed?
        if (file_exists($path)) {
            // Does the willow file NOT already exist?
            if (!file_exists(__DIR__ . '/../../willow')) {
                // Create the willow symlink file
                symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
            }

            $cli = new CLImate();
            $cli->bold()->white('To run the sample and view the docs type:');
            $cli->bold()->lightGray('cd ' . $projectName);
            if (WillowCommands::isWindows()) {
                $cli->bold()->lightGray('willow willow:sample');
                $cli->bold()->lightGray('willow willow:docs');
            } else {
                $cli->bold()->lightGray('./willow willow:sample');
                $cli->bold()->lightGray('./willow willow:docs');
            }
        }
    }

    /**
     * Show Willow fancy Banner
     */
    public static function fancyBanner(): void
    {
        // Display Willow's fancy message
        $cli = new CLImate();
        $cli->forceAnsiOn();
        $cli->green()->border('*', 55);

        // Animation in Windows chokes on preg_replace.
        if (WillowCommands::isWindows()) {
            $cli->bold()->lightGreen('Willow');
        } else {
            $cli->addArt(__DIR__);
            $cli->bold()->lightGreen()->animation('willow')->speed(200)->enterFrom('left');
        }

        $cli->backgroundGreen()->lightGray(' 🌳 https://github.com/RyanNerd/willow 🌳');
        $cli->backgroundGreen()->lightGray(' 🤲 https://www.patreon.com/bePatron?u=3985594 🤲');
        $cli->green()->border('*', 55);
        $cli->bold()->white()->inline('Thanks for installing ');
        $cli->bold()->lightGreen()->inline('Willow');
        $cli->bold()->white('!');
    }
}
