<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Throwable;

class ForgeController extends ForgeBase
{
    /**
     * Forge the Controller code given the tableName and the route
     * @param string $tableName
     * @param string $route
     */
    final public function forgeController(string $tableName, string $route): void {
        try {
            $className = self::getClassNameFromTable($tableName);

            // Render the Controller code.
            $controllerCode = $this->render('Controller.php.twig', ['class_name' => $className, 'route' => $route]);

            // Create the controller directory
            $controllerPath = self::makeControllerDirectory($className);

            // Save the Controller code file into the Controllers directory.
            $controllerFile = $controllerPath . '/' . $className . 'Controller.php';
            if (file_put_contents($controllerFile, $controllerCode) === false) {
                CliBase::showThrowableAndDie(
                    new Exception('Unable to create: ' . $controllerFile)
                );
            }
        } catch (Throwable $e) {
            CliBase::showThrowableAndDie($e);
        }
    }
}
