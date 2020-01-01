<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait ControllerTrait
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * Forge the Controller code given the tableName and optionally the route
     *
     * @param string $tableName
     * @param string|null $route
     * @return string|null
     */
    protected function forgeController(string $tableName, ?string $route): ?string
    {
        // Is route not given? Make the route the lower case of the table name.
        if ($route === null) {
            $route = strtolower($tableName);
        }

        // Format the table name as a class
        $className = ucfirst($tableName);

        // Render the Controller code.
        try {
            $controllerCode = $this->twig->render('Controller.php.twig', [
                    'class_name' => $className,
                    'route' => $route
                ]
            );
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        $controllerPath = __DIR__ . '/../../../Controllers/' . $className;

        if (is_dir($controllerPath) === false) {
            if (mkdir($controllerPath) === false) {
                return 'Unable to create directory: ' . $controllerPath;
            }
        }

        // Save the Controller code file into the Controllers directory.
        if (file_put_contents($controllerPath . '/' . $className . 'Controller.php', $controllerCode) === false) {
            return 'Unable to create: ' . $controllerPath . 'Controller.php';
        }

        return null;
    }
}
