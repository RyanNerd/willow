<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait GetActionTrait
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * Forge the GetAction code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeGetAction(string $entity): ?string
    {
        // Format the GetAction class name
        $className = ucfirst($entity);

        // Render the GetAction code.
        try {
            $getActionCode = $this->twig->render('GetAction.php.twig', [
                    'class_name' => $className
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

        // Save the getAction code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'GetAction.php', $getActionCode) === false) {
            return 'Unable to create: ' . $controllerPath .  '/' . $className . 'GetAction.php';
        }

        return null;
    }
}
