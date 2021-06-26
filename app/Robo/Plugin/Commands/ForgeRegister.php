<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

class ForgeRegister extends ForgeBase
{
    private const REGISTER_CONTROLLERS_PATH = __DIR__. '/../../../Middleware/RegisterControllers.php';
    /**
     * Forge the RegisterControllers code
     * Note: This should be executed last since it relies on the existance of established controller directories
     */
    final public function forgeRegisterControllers(): void {
        try {
            $controllerPath = self::CONTROLLERS_PATH . '*';
            $dirList = glob($controllerPath, GLOB_ONLYDIR);

            // Is there an error getting directory list or no directories?
            if ($dirList === false || count($dirList) === 0) {
                CliBase::showThrowableAndDie(
                    new Exception('No controllers found at ' . $controllerPath . PHP_EOL . 'Nothing to do.')
                );
            }

            // Build a list of classes based on the controlller's directory structure
            $classList = [];
            foreach ($dirList as $dirName) {
                $classList[] = basename($dirName);
            }

            // Render the RegisterControllers code.
            $registerControllersCode = $this->render('RegisterControllers.php.twig', ['class_list' => $classList]);

            // Save the registerControllersCode overwriting Middleware/RegisterControllers.php
            if (file_put_contents(self::REGISTER_CONTROLLERS_PATH, $registerControllersCode) === false) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . self::REGISTER_CONTROLLERS_PATH)
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
