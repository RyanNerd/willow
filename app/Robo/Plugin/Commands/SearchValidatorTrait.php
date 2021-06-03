<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait SearchValidatorTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the SearchValidator code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeSearchValidator(string $entity): ?string
    {
        // Format the SearchValidator class name
        $className = ucfirst($entity);

        // Render the SearchValidator code.
        try {
            $searchValidatorCode = $this->twig->render('SearchValidator.php.twig', [
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
        if (file_put_contents($controllerPath . '/' . $className . 'SearchValidator.php', $searchValidatorCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'SearchValidator.php';
        }

        return null;
    }
}
