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

        // Get a CLI object
        $cli = new CLImate();

        // Display Willow's fancy message
        self::fancyBanner($cli);

        // Create symlink to Robo
        $path = __DIR__ . '/../../vendor/bin/robo';
        // Has Robo been fully installed?
        if (file_exists($path)) {
            $isWindows = WillowCommands::isWindows();

            // Create the willow symlink file
            try {
                if ($isWindows) {
                    $symlinkCreated = symlink(__DIR__ . '/../../vendor/bin/robo.bat', 'willow');
                } else {
                    $symlinkCreated = symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
                }
            } catch (\Exception $exception) {
                $symlinkCreated = false;
            }

            // Did the symlink get created?
            if (!$symlinkCreated) {
                $cli->bold()->lightRed('Unable to create a symlink for the `willow` command.');
                if ($isWindows) {
                    $cli->bold()->white('Make sure you are running PowerShell as an administrator,');
                    $cli->bold()->white('or have developer mode enabled.');
                } else {
                    $cli->bold()->white('You may not have rights to create symlinks.');
                }
                $cli->bold()->white('You will need to create the willow symlink manually.');
            }

            $cli->bold()->white('To run the sample and view the docs type:');
            $cli->bold()->lightGray('cd ' . $projectName);

            if (!$symlinkCreated) {
                $cli->bold()->lightGray('./willow willow:sample');
                $cli->bold()->lightGray('./willow willow:docs');
            } else {
                $roboPath = $isWindows ? './vendor/bin/robo.bat' : './vendor/bin/robo';
                $cli->bold()->lightGray("$roboPath willow:sample");
                $cli->bold()->lightGray("$roboPath willow:docs");
            }
        }
    }

    /**
     * Show Willow fancy Banner
     *
     * @param CLImate|null $cli
     */
    public static function fancyBanner(CLImate $cli = null): void
    {
        if ($cli === null) {
            $cli = new CLImate();
        }

        // Display Willow's fancy message
        $cli->forceAnsiOn();
        $cli->green()->border('*', 55);

        // Text art in Windows chokes on preg_replace.
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
