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
     * Composer hook that fires when composer create-project has finished.
     */
    public static function postCreateProjectCmd($event): void
    {
        // Get a CLI object
        $cli = new CLImate();
        $cli->br();

        // Is the user running windows?
        if (self::isWindows()) {
            // Is the user running under WSL? If not then display warning message and die.
            if (!self::isWSL()) {
                $cli->br();
                $cli
                    ->bold()
                    ->lightYellow(
                        'Warning: The Willow command line will only work in Linux, Mac or Windows running WSL'
                    );
                $cli
                    ->bold()
                    ->lightYellow(
                        'It appears you are running Windows without WSL. Please install WSL on Windows,'
                    );
                $cli->bold()->lightYellow('then re-run `composer create-project ...`');
                $cli
                    ->bold()
                    ->lightYellow(
                        'See https://itsfoss.com/run-linux-commands-in-windows for methods of installing WSL'
                    );
                $cli->br();
                die();
            }
        }

        // Display Willow's fancy message
        self::fancyBanner($cli);

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');
        $baseDir = preg_replace('/vendor$/', '', $vendorDir);
        $projectName = basename($baseDir);

        // Create the willow symlink file
        try {
            $symlinkCreated = symlink($binDir . '/robo', 'willow');
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

        $cli->br();
        $cli->bold()->lightGray('# change directory to ' . $projectName);
        $cli->bold()->lightGreen('cd ' . $projectName)->br();

        // Display what commands to run depending on if the symlink was created and the O/S
        if ($symlinkCreated) {
            $cli->bold()->lightGray('# Run the sample app');
            $cli->bold()->lightGreen('./willow sample')->br();
            $cli->bold()->lightGray('# Open the docs on the web');
            $cli->bold()->lightGreen('./willow docs')->br();
            $cli->bold()->lightGray('# List available commands');
            $cli->bold()->lightGreen('./willow list')->br();
        } else {
            $cli->error('Unable to create a symlink to robo. You will need to run robo in vendor\bin')->br();
            $cli->bold()->lightGray('# Run the sample app');
            $cli->bold()->lightGreen('./vendor/bin/robo sample');
            $cli->bold()->lightGray('# Open the docs on the web');
            $cli->bold()->lightGray('./vendor/bin/robo docs');
            $cli->bold()->lightGray('# List available commands');
            $cli->bold()->lightGray('./vendor/bin/robo list');
        }
    }

    /**
     * Show Willow fancy Banner
     * @param CLImate $cli
     */
    public static function fancyBanner(CLImate $cli): void
    {
        // Display Willow's fancy message
        $cli->forceAnsiOn();
        $cli->green()->border('*', 55);

        $cli->addArt(__DIR__);
        $cli->bold()->lightGreen()->animation('willow')->speed(200)->enterFrom('left');

        $cli->backgroundGreen()->lightGray('  https://github.com/RyanNerd/willow');
        $cli->green()->border('*', 55);
        $cli->bold()->white()->inline('Thanks for installing ');
        $cli->bold()->lightGreen()->inline('Willow');
        $cli->bold()->white('!');
    }

    /**
     * Return true if we are running on Windows in the Linux Subsystem (WSL)
     * @see https://stackoverflow.com/q/38086185/4323201
     * @return bool
     */
    public static function isWSL(): bool
    {
        if (false === ($procFileVersion = file_get_contents('/proc/version'))) {
            if (false === ($procFileVersion = file_get_contents('/proc/sys/kernel/osrelease'))) {
                return false;
            }
        }
        if (str_contains($procFileVersion, 'WSL') || str_contains($procFileVersion, 'Microsoft')) {
            return true;
        }
        return false;
    }

    /**
     * Returns true if the current O/S is any flavor of Windows
     *
     * @return bool
     */
    public static function isWindows(): bool {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}
