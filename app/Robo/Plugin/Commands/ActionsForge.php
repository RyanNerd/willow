<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Twig\Environment as Twig;
use Throwable;
use Exception;

class ActionsForge
{
    protected Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Forge the GetAction code given the table name.
     * @param string $table
     */
    public function forgeGetAction(string $table)
    {
        try {
            // Format the GetAction class name
            $className = ucfirst($table);
            // Render the GetAction code.
            $getActionCode = $this->twig->render(
                'GetAction.php.twig',
                [
                    'class_name' => $className
                ]
            );
            $controllerPath = $this->getControllersPath() . $className;
            if (is_dir($controllerPath) === false) {
                if (mkdir($controllerPath) === false) {
                    $this->forgeActionError(new Exception('Unable to create directory: ' . $controllerPath), $table);
                }
            }
            // Save the getAction code file into the Controllers/ directory.
            if (file_put_contents($controllerPath . '/' . $className . 'GetAction.php', $getActionCode) === false) {
                $this->forgeActionError(
                    new Exception('Unable to create: ' . $controllerPath .  '/' . $className . 'GetAction.php'), $table
                );
            }
        } catch (Throwable $e) {
            $this->forgeActionError($e, $table);
        }
    }


    /**
     * Convenience function to return the path to the Controllers directory
     * @return string
     */
    protected function getControllersPath(): string {
        return  __DIR__ . '/../../../Controllers/';
    }

    /**
     * Called when an exception is encountered.
     * @param Throwable $throwable
     * @param string $table
     */
    protected function forgeActionError(Throwable $throwable, string $table) {
        RoboBase::showThrowableAndDie($throwable, ["Action creation error for: $table"]);
    }
}
