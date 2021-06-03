<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait RegisterControllersTrait
{
    protected Environment $twig;

    /**
     * Forge the RegisterControllers code
     * @warning Destructive code. Existing RegisterControllers.php file will be overwritten
     * @return string|null Error message string on failure or null on success
     */
    protected function forgeRegisterControllers(): ?string
    {
        $controllerPath = __DIR__ . '/../../../Controllers';
        $dirList = scandir($controllerPath);
        $classList = [];
        foreach ($dirList as $fileName) {
            if (is_dir($controllerPath . '/' . $fileName)) {
                $classList[] = $fileName;
            }
        }

        // Render the RegisterControllers code.
        try {
            $registerControllersCode = $this->twig->render(
                'RegisterControllers.php.twig',
                [
                    'class_list' => $classList
                ]
            );
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        $registerControllersPath = __DIR__ . '/../../../Middleware/RegisterControllers.php';

        // Save the registerControllersCode overwriting Middleware/RegisterControllers.php
        if (file_put_contents($registerControllersPath, $registerControllersCode) === false) {
            return 'Unable to create: ' . $registerControllersPath;
        }

        return null;
    }
}