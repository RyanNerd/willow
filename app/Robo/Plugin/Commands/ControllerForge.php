<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment as Twig;
use Exception;

class ControllerForge extends ForgeBase
{
    protected Twig $twig;

    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }

    /**
     * Forge the Controller code given the tableName and the route
     * @param string $tableName
     * @param string $route
     */
    final public function forgeController(string $tableName, string $route): void {
        try {
            // Format the table name as a class
            $className = ucfirst($tableName);
            // Render the Controller code.
            $controllerCode = $this->twig->render(
                'Controller.php.twig',
                [
                    'class_name' => $className,
                    'route' => $route
                ]
            );
            // Create the controller directory
            $controllerPath = self::CONTROLLERS_PATH . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeError(
                        new Exception('Unable to create directory: ' . $controllerPath)
                    );
                }
            }
            // Save the Controller code file into the Controllers directory.
            $controllerFile = $controllerPath . '/' . $className . 'Controller.php';
            if (file_put_contents($controllerFile, $controllerCode) === false) {
                $this->forgeError(
                    new Exception('Unable to create: ' . $controllerFile)
                );
            }
        } catch (Throwable $e) {
            $this->forgeError($e);
        }
    }
}
