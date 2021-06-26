<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Exception;
use Illuminate\Support\Str;
use Throwable;
use Twig\Environment as Twig;

class ForgeController
{
    private const CONTROLLERS_PATH = __DIR__ . '/../../../Controllers/';

    public function __construct(private Twig $twig) {
    }

    /**
     * Forge the Controller code given the tableName and the route
     * @param string $tableName
     * @param string $route
     */
    final public function forgeController(string $tableName, string $route): void {
        try {
            // Format the table name as a class
            $className = ucfirst(Str::camel($tableName));
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
                    CliBase::showThrowableAndDie(
                        new Exception('Unable to create directory: ' . $controllerPath)
                    );
                }
            }
            // Save the Controller code file into the Controllers directory.
            $controllerFile = $controllerPath . $className . 'Controller.php';
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
