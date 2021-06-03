<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait WriteValidatorTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the WriteValidator code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeWriteValidator(string $entity): ?string
    {
        // Format the WriteValidator class name
        $className = ucfirst($entity);

        // Render the WriteValidator code.
        try {
            $writeValidatorCode = $this->twig->render('WriteValidator.php.twig', [
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

        // Save the WriteValidator code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'WriteValidator.php', $writeValidatorCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'WriteValidator.php';
        }

        return null;
    }
}
