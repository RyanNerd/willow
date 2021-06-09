<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Throwable;
use Twig\Environment as Twig;
use Willow\Robo\Plugin\Commands\RoboBase;
use Exception;

trait ForgeControllerTrait
{
    protected Twig $twig;

    /**
     * Forge the Controller code given the tableName and the route
     * @param string $tableName
     * @param string $route
     */
    protected function forgeController(string $tableName, string $route)
    {
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
            $controllerPath = self::_getContainer()->get('controllers_path') . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeControllerError(
                        new Exception('Unable to create directory: ' . $controllerPath), $tableName
                    );
                }
            }
            // Save the Controller code file into the Controllers directory.
            $controllerFile = $controllerPath . '/' . $className . 'Controller.php';
            if (file_put_contents($controllerFile, $controllerCode) === false) {
                $this->forgeControllerError(
                    new Exception('Unable to create: ' . $controllerFile), $tableName
                );
            }
        } catch (Throwable $e) {
            $this->forgeControllerError($e, $tableName);
        }
    }

    /**
     * Called when an exception is encountered.
     * @param Throwable $throwable
     * @param string $table
     */
    protected function forgeControllerError(Throwable $throwable, string $table) {
        RoboBase::showThrowableAndDie($throwable, ["Controller creation error for: $table"]);
    }
}
