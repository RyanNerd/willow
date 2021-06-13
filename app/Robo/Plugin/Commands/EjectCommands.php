<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use League\CLImate\TerminalObject\Dynamic\Input;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

class EjectCommands extends RoboBase
{
    private const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    /**
     * Remove Sample controller, routes, & other artifacts from the project
     */
    final public function eject(): void {
        $cli = $this->cli;
        $cli->br();
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white()
            ->border('*');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white('eject is a destructive operation. It removes the Sample controller, route, etc.');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white('It will also overwrite the RegisterControllers.php file.');
        $cli
            ->bold()
            ->backgroundLightRed()
            ->white()
            ->border('*');
        $cli->br();
        /** @var Input $input */
        $input = $cli->bold()->lightGray()->confirm('Are you sure you want to proceed?');
        if (!$input->confirmed()) {
            die();
        }

        $sampleDirPath = self::CONTROLLERS_PATH . 'Sample';
        array_map('unlink', glob("$sampleDirPath/*.*"));
        if (file_exists($sampleDirPath)) {
            if (is_dir($sampleDirPath)) {
                rmdir($sampleDirPath);
            }
        }

        // Get a file list of any controllers
        $dirList = glob(self::CONTROLLERS_PATH . '*', GLOB_ONLYDIR);
        // No need to register controllers if there are none.
        if ($dirList === false || count($dirList) === 0) {
            $cli->lightYellow('INFO: No controllers exist to re-register');
        } else {
            // Rebuild RegisterControllers.php
            $loader = new FilesystemLoader(__DIR__ . '/Templates');
            $twig = new Twig($loader);
            $registerControllers = new RegisterForge($twig);
            $registerControllers->forgeRegisterControllers();
        }
    }
}
