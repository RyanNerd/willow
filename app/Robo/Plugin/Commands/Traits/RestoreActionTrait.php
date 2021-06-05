<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands\Traits;

use Throwable;
use Twig\Environment;

trait RestoreActionTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the RestoreAction code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeRestoreAction(string $entity): ?string
    {
        // Format the RestoreAction class name
        $className = ucfirst($entity);

        // Render the RestoreAction code.
        try {
            $restoreActionCode = $this->twig->render('RestoreAction.php.twig', [
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

        // Save the restoreAction code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'RestoreAction.php', $restoreActionCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'RestoreAction.php';
        }

        return null;
    }
}
