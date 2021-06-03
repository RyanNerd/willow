<?php
declare(strict_types=1);

namespace Willow\Robo\Plugin\Commands;

use Throwable;
use Twig\Environment;

trait DeleteActionTrait
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Forge the DeleteAction code given the entity (table/view) name.
     *
     * @param string $entity
     * @return string|null
     */
    protected function forgeDeleteAction(string $entity): ?string
    {
        // Format the DeleteAction class name
        $className = ucfirst($entity);

        // Render the DeleteAction code.
        try {
            $deleteActionCode = $this->twig->render('DeleteAction.php.twig', [
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

        // Save the deleteAction code file into the Controllers/ directory.
        if (file_put_contents($controllerPath . '/' . $className . 'DeleteAction.php', $deleteActionCode) === false) {
            return 'Unable to create: ' . $controllerPath . '/' . $className . 'DeleteAction.php';
        }

        return null;
    }
}
