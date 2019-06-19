<?php
declare(strict_types=1);

namespace Willow\Robo;

use Composer\Script\Event;
use League\CLImate\CLImate;
use Willow\Robo\Plugin\Commands\WillowCommands;

/**
 * Composer script
 */
class Script
{
    /**
     * Composer hook that fires when composer create-project has finished.
     *
     * @param $event
     */
    public static function postCreateProjectCmd(Event $event): void
    {
        $args = $event->getArguments();

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

        $isWindows = WillowCommands::isWindows();
        $symlinkCreated = false;

        // Are we NOT running in Windows?
        if (!$isWindows) {
            // Create the willow symlink file
            try {
                $symlinkCreated = symlink(__DIR__ . '/../../vendor/bin/robo', 'willow');
            } catch (\Exception $exception) {
                $symlinkCreated = false;
            }

            // Did the symlink NOT get created?
            if (!$symlinkCreated) {
                $cli->br();
                $cli->bold()->lightYellow('Warning: Unable to create a symlink for the `willow` command.');
                $cli->bold()->white('You may not have rights to create symlinks.');
                $cli->bold()->white('You will need to create the willow symlink manually.');
                $cli->br();
            }
        }

        $cli->bold()->white('To run the sample and view the docs type:');
        $cli->bold()->lightGray('cd ' . $projectName);

        // Display what commands to run depending on if the symlink was created and the O/S
        if ($symlinkCreated) {
            $cli->bold()->lightGray('./willow willow:sample');
            $cli->bold()->lightGray('./willow willow:docs');
            $cli->bold()->white('For a list of available commands run: ./willow list');
        } else {
            if ($isWindows) {
                $cli->bold()->lightGray('You must manually add robo to your path:' . __DIR__. '\vendor\bin\robo.bat');
                $cli->bold()->lightGray('Then run:');
                $cli->bold()->lightGray('robo willow:sample');
                $cli->bold()->lightGray('robo willow:docs');
                $cli->bold()->white('For a list of available commands run: robo list');
            } else {
                $cli->bold()->lightGray('./vendor/bin/robo willow:sample');
                $cli->bold()->lightGray('./vendor/bin/robo willow:docs');
                $cli->bold()->white('For a list of available commands run: ./vendor/bin/robo list');
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

        $cli->backgroundGreen()->lightGray('  https://github.com/RyanNerd/willow');
        $cli->backgroundGreen()->lightGray('  https://www.patreon.com/bePatron?u=3985594');
        $cli->green()->border('*', 55);
        $cli->bold()->white()->inline('Thanks for installing ');
        $cli->bold()->lightGreen()->inline('Willow');
        $cli->bold()->white('!');
    }
}
