<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

use Twig\Environment as Twig;

class RegisterForge extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the RegisterControllers code
     */
    final public function forgeRegisterControllers(): void {
        $controllerPath = self::CONTROLLERS_PATH . '*';
        $dirList = glob($controllerPath, GLOB_ONLYDIR);
        // Error getting directory list or no directories.
        if ($dirList === false || count($dirList) === 0) {
            $this->forgeError(new Exception('No controllers found at ' . $controllerPath . PHP_EOL . 'Nothing to do.'));
        }

        $classList = [];
        foreach ($dirList as $dirName) {
            $classList[] = basename($dirName);
        }

        // Render the RegisterControllers code.
        try {
            $registerControllersCode = $this->twig->render(
                'RegisterControllers.php.twig',
                [
                    'class_list' => $classList
                ]
            );
            $registerControllersPath = __DIR__. '/../../../Middleware/RegisterControllers.php';

            // Save the registerControllersCode overwriting Middleware/RegisterControllers.php
            if (file_put_contents($registerControllersPath, $registerControllersCode) === false) {
                $this->forgeError(new Exception('RegisterControllers - Unable to create: ' . $registerControllersPath));
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }
}
