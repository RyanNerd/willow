<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait RestoreValidatorTrait
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * Forge the RestoreValidator code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeRestoreValidator(string $entity): ?string
    {
        // Format the RestoreValidator class name
        $className = ucfirst($entity);

        // Render the RestoreValidator code.
        try {
            $restoreActionCode = $this->twig->render('RestoreValidator.php.twig', [
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
        if (file_put_contents($controllerPath . '/' . $className . 'RestoreValidator.php', $restoreActionCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'RestoreValidator.php';
        }

        return null;
    }
}
